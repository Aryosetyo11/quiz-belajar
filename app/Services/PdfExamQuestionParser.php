<?php

namespace App\Services;

use App\Models\ExamQuestion;
use RuntimeException;

class PdfExamQuestionParser
{
    /**
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    public function import(string|false $path): array
    {
        if (! is_string($path) || ! file_exists($path)) {
            throw new RuntimeException('File PDF tidak dapat dibaca.');
        }

        $pdf = file_get_contents($path);
        if (! is_string($pdf) || ! str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('File bukan dokumen PDF yang valid.');
        }

        $text = $this->extractText($pdf);
        if (trim($text) === '') {
            throw new RuntimeException('Teks PDF tidak terbaca. Pastikan teksnya bisa diseleksi, bukan hasil scan/foto. PDF hasil scan perlu OCR terlebih dahulu.');
        }

        $blocks = [];
        $currentBlock = null;
        foreach (preg_split('/\R/u', $text) ?: [] as $line) {
            $line = trim($line);
            if (preg_match('/^(?:soal\s*)?(\d{1,3})\s*[.)\]:-]\s*(.*)$/iu', $line, $questionStart) === 1) {
                if ($currentBlock !== null) {
                    $blocks[] = $currentBlock;
                }

                $currentBlock = ['number' => (int) $questionStart[1], 'lines' => [$questionStart[2]]];

                continue;
            }

            if ($currentBlock !== null && $line !== '') {
                $currentBlock['lines'][] = $line;
            }
        }

        if ($currentBlock !== null) {
            $blocks[] = $currentBlock;
        }

        $questions = [];
        foreach ($blocks as $block) {
            $question = $this->parseQuestionBlock($block['lines'], $block['number']);
            if ($question !== null) {
                $questions[] = ['number' => $block['number'], 'question' => $question];
            }
        }

        if ($questions === []) {
            throw new RuntimeException('PDF terbaca, tetapi belum ada soal yang cocok. Gunakan nomor soal (contoh: 1.), label pilihan A–D, dan kunci jawaban untuk PG. Esai dapat ditandai [ESAI] dan memakai Pedoman Jawaban.');
        }

        usort($questions, static fn (array $first, array $second): int => $first['number'] <=> $second['number']);

        return array_map(static fn (array $item): array => $item['question'], $questions);
    }

    private function extractText(string $pdf): string
    {
        $text = '';
        $decodedBytes = 0;
        preg_match_all('/<<(.*?)>>\s*stream(?:\r\n|\r|\n)(.*?)endstream/s', $pdf, $streams, PREG_SET_ORDER);

        foreach ($streams as $streamMatch) {
            $stream = rtrim($streamMatch[2], "\r\n");
            preg_match_all('/\/(ASCII85Decode|A85|ASCIIHexDecode|AHx|FlateDecode|Fl)\b/', $streamMatch[1], $filters);

            foreach ($filters[1] ?? [] as $filter) {
                $decoded = match ($filter) {
                    'ASCII85Decode', 'A85' => $this->decodeAscii85($stream),
                    'ASCIIHexDecode', 'AHx' => $this->decodeAsciiHex($stream),
                    'FlateDecode', 'Fl' => @gzuncompress($stream) ?: @gzinflate($stream),
                };

                if (! is_string($decoded)) {
                    $stream = '';
                    break;
                }

                $stream = $decoded;
            }

            $decodedBytes += strlen($stream);
            if ($decodedBytes > 25_000_000) {
                throw new RuntimeException('Isi PDF terlalu besar untuk diproses.');
            }

            if ($stream === '') {
                continue;
            }

            preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj|\[(.*?)\]\s*TJ|<([A-Fa-f0-9\s]+)>\s*Tj/s', $stream, $operators, PREG_SET_ORDER);
            foreach ($operators as $operator) {
                preg_match_all('/\((?:\\\\.|[^\\\\)])*\)|<([A-Fa-f0-9\s]+)>/s', $operator[0], $strings, PREG_SET_ORDER);
                foreach ($strings as $string) {
                    $value = $string[0];
                    $text .= str_starts_with($value, '<')
                        ? $this->decodeHexString(substr($value, 1, -1))
                        : $this->decodeLiteral(substr($value, 1, -1));
                }
                $text .= "\n";
            }
        }

        return $text;
    }

    private function decodeAscii85(string $encoded): string|false
    {
        $encoded = preg_replace('/\s+/', '', $encoded) ?? $encoded;
        $encoded = preg_replace('/^<~/', '', $encoded) ?? $encoded;
        $encoded = explode('~>', $encoded, 2)[0];
        $decoded = '';
        $group = [];

        foreach (str_split($encoded) as $character) {
            if ($character === 'z') {
                if ($group !== []) {
                    return false;
                }
                $decoded .= "\0\0\0\0";

                continue;
            }

            $value = ord($character) - 33;
            if ($value < 0 || $value > 84) {
                return false;
            }

            $group[] = $value;
            if (count($group) === 5) {
                $decoded .= $this->ascii85GroupToBytes($group, 4);
                $group = [];
            }
        }

        if (count($group) === 1) {
            return false;
        }

        if ($group !== []) {
            $length = count($group) - 1;
            while (count($group) < 5) {
                $group[] = 84;
            }
            $decoded .= $this->ascii85GroupToBytes($group, $length);
        }

        return $decoded;
    }

    /** @param array<int, int> $group */
    private function ascii85GroupToBytes(array $group, int $length): string
    {
        $value = 0;
        foreach ($group as $digit) {
            $value = ($value * 85) + $digit;
        }

        return substr(pack('N', $value), 0, $length);
    }

    private function decodeAsciiHex(string $encoded): string|false
    {
        $hex = preg_replace('/\s+/', '', $encoded) ?? $encoded;
        $hex = explode('>', $hex, 2)[0];
        if (preg_match('/^[A-Fa-f0-9]*$/', $hex) !== 1) {
            return false;
        }
        if (strlen($hex) % 2 !== 0) {
            $hex .= '0';
        }

        $decoded = hex2bin($hex);

        return is_string($decoded) ? $decoded : false;
    }

    private function decodeLiteral(string $literal): string
    {
        $decoded = preg_replace_callback('/\\\\([\\\\()nrtbf]|[0-7]{1,3}|\r?\n)/', static function (array $match): string {
            return match ($match[1]) {
                'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\x0c",
                default => ctype_digit($match[1]) ? chr(octdec($match[1]) % 256) : $match[1],
            };
        }, $literal) ?? $literal;

        return mb_check_encoding($decoded, 'UTF-8')
            ? $decoded
            : mb_convert_encoding($decoded, 'UTF-8', 'Windows-1252');
    }

    private function decodeHexString(string $hex): string
    {
        $hex = preg_replace('/\s+/', '', $hex) ?? $hex;
        if (strlen($hex) % 2 !== 0) {
            $hex .= '0';
        }
        $decoded = hex2bin($hex);
        if (! is_string($decoded)) {
            return '';
        }

        if (str_starts_with($decoded, "\xFE\xFF")) {
            return mb_convert_encoding(substr($decoded, 2), 'UTF-8', 'UTF-16BE');
        }

        if (str_contains($decoded, "\0")) {
            return mb_convert_encoding($decoded, 'UTF-8', 'UTF-16BE');
        }

        return mb_convert_encoding($decoded, 'UTF-8', 'Windows-1252');
    }

    /**
     * @param  array<int, string>  $lines
     * @return array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}|null
     */
    private function parseQuestionBlock(array $lines, int $number): ?array
    {
        $prompt = [];
        $options = [];
        $sampleAnswer = [];
        $answerKey = null;
        $points = 10;
        $isEssay = false;
        $inSampleAnswer = false;
        $lastOption = null;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if ($line === '' || preg_match('/^soal\s+(?:pilihan\s+ganda|pg|esai|uraian)$/iu', $line) === 1) {
                continue;
            }

            if (preg_match('/^\[(?:esai|essay|uraian)\]\s*/iu', $line) === 1) {
                $isEssay = true;
                $line = preg_replace('/^\[(?:esai|essay|uraian)\]\s*/iu', '', $line) ?? $line;
            }

            $line = preg_replace('/^(?:\[(?:pg|pilihan\s+ganda|multiple\s+choice)\]|(?:esai|essay|uraian)\s*:)\s*/iu', '', $line) ?? $line;

            if (preg_match('/\b(?:poin|points?|nilai)\s*[:=]?\s*(\d+)\b/iu', $line, $pointMatch) === 1) {
                $points = max(1, min(100, (int) $pointMatch[1]));
                $line = preg_replace('/\s*\b(?:poin|points?|nilai)\s*[:=]?\s*\d+\b/iu', '', $line) ?? $line;
            }

            if (preg_match('/\b(?:kunci(?:\s+jawaban)?|jawaban(?:\s+benar)?|correct\s+answer|answer(?:\s*key)?)\s*[:=]\s*([A-D])\b/iu', $line, $answerMatch) === 1) {
                $answerKey = strtoupper($answerMatch[1]);
                $line = preg_replace('/\s*\b(?:kunci(?:\s+jawaban)?|jawaban(?:\s+benar)?|correct\s+answer|answer(?:\s*key)?)\s*[:=]\s*[A-D]\b/iu', '', $line) ?? $line;
            }

            if (preg_match('/^(?:pedoman(?:\s+jawaban)?|kunci\s+esai|rubrik(?:\s+jawaban)?)\s*[:=]\s*(.*)$/iu', $line, $sampleMatch) === 1) {
                $isEssay = true;
                $inSampleAnswer = true;
                $lastOption = null;
                if (trim($sampleMatch[1]) !== '') {
                    $sampleAnswer[] = trim($sampleMatch[1]);
                }

                continue;
            }

            preg_match_all('/(?<!\S)\(?([A-D])[.)]\s*/iu', $line, $inlineOptionMatches, PREG_OFFSET_CAPTURE);
            $inlineLetters = array_map(static fn (array $match): string => strtoupper($match[0]), $inlineOptionMatches[1] ?? []);
            if ($options === [] && $inlineLetters === ['A', 'B', 'C', 'D']) {
                $firstOptionOffset = $inlineOptionMatches[0][0][1];
                $questionText = trim(substr($line, 0, $firstOptionOffset));
                if ($questionText !== '') {
                    $prompt[] = $questionText;
                }

                foreach ($inlineOptionMatches[0] as $optionIndex => [$marker, $offset]) {
                    $valueOffset = $offset + strlen($marker);
                    $nextOffset = $inlineOptionMatches[0][$optionIndex + 1][1] ?? strlen($line);
                    $options[$inlineLetters[$optionIndex]] = trim(substr($line, $valueOffset, $nextOffset - $valueOffset));
                }

                $lastOption = 'D';

                continue;
            }

            if (preg_match('/^\(?([A-D])\s*[.)\]:-]\s*(.*)$/iu', $line, $optionMatch) === 1 && $index > 0) {
                $letter = strtoupper($optionMatch[1]);
                $options[$letter] = trim($optionMatch[2]);
                $lastOption = $letter;
                $inSampleAnswer = false;

                continue;
            }

            if ($line === '') {
                continue;
            }

            if ($inSampleAnswer) {
                $sampleAnswer[] = $line;
            } elseif ($lastOption !== null) {
                $options[$lastOption] = trim(($options[$lastOption] ?? '').' '.$line);
            } else {
                $prompt[] = $line;
            }
        }

        $promptText = trim(preg_replace('/\s+/u', ' ', implode(' ', $prompt)) ?? '');
        if ($promptText === '') {
            return null;
        }

        if ($isEssay || $options === []) {
            return [
                'prompt' => $promptText,
                'type' => ExamQuestion::TYPE_ESSAY,
                'options' => null,
                'answer_key' => null,
                'sample_answer' => $sampleAnswer === [] ? null : trim(preg_replace('/\s+/u', ' ', implode(' ', $sampleAnswer)) ?? ''),
                'points' => $points,
            ];
        }

        if ($answerKey === null || ! isset($options[$answerKey])) {
            throw new RuntimeException("Kunci jawaban PG pada soal PDF nomor {$number} belum dikenali. Tambahkan baris seperti ‘Kunci: A’ atau ‘Jawaban: A’.");
        }

        foreach (['A', 'B', 'C', 'D'] as $letter) {
            $options[$letter] = $options[$letter] ?? '-';
        }

        return [
            'prompt' => $promptText,
            'type' => ExamQuestion::TYPE_MULTIPLE_CHOICE,
            'options' => $options,
            'answer_key' => $answerKey,
            'sample_answer' => null,
            'points' => $points,
        ];
    }
}
