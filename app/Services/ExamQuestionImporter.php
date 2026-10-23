<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ExamQuestionImporter
{
    /**
     * @return array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}>
     */
    public function import(UploadedFile $file): array
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'xlsx' => $this->importSpreadsheet($file->getRealPath()),
            'pdf' => $this->importPdf($file->getRealPath()),
            default => throw new RuntimeException('Format file belum didukung. Gunakan PDF atau Excel .xlsx.'),
        };
    }

    /**
     * @return array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}>
     */
    private function importSpreadsheet(string|false $path): array
    {
        if (! is_string($path)) {
            throw new RuntimeException('File Excel tidak dapat dibaca.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak valid atau rusak.');
        }

        try {
            $sheetStats = $zip->statName('xl/worksheets/sheet1.xml');
            if (is_array($sheetStats) && ($sheetStats['size'] ?? 0) > 5_000_000) {
                throw new RuntimeException('Isi lembar Excel terlalu besar untuk diproses.');
            }

            $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');

            if ($sheet === false) {
                throw new RuntimeException('Lembar pertama Excel tidak ditemukan.');
            }

            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

            if ($sharedXml !== false) {
                $shared = simplexml_load_string($sharedXml);

                if ($shared instanceof SimpleXMLElement) {
                    foreach ($shared->xpath('//*[local-name()="si"]') ?: [] as $item) {
                        $sharedStrings[] = implode('', array_map('strval', $item->xpath('.//*[local-name()="t"]') ?: []));
                    }
                }
            }

            $xml = simplexml_load_string($sheet);
            if (! $xml instanceof SimpleXMLElement) {
                throw new RuntimeException('Isi lembar Excel tidak dapat dibaca.');
            }

            $rows = [];
            foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
                $cells = [];
                foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                    $reference = (string) $cell['r'];
                    preg_match('/^[A-Z]+/', $reference, $match);
                    $column = $this->columnIndex($match[0] ?? 'A');
                    $value = (string) ($cell->xpath('./*[local-name()="v"]')[0] ?? '');

                    if ((string) $cell['t'] === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    } elseif ((string) $cell['t'] === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]') ?: []));
                    }

                    $cells[$column] = trim($value);
                }
                if ($cells !== []) {
                    $rows[] = $cells;
                }
            }

            return $this->questionsFromRows($rows);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}>
     */
    private function questionsFromRows(array $rows): array
    {
        if (count($rows) < 2) {
            throw new RuntimeException('Excel harus memiliki baris judul kolom dan minimal satu baris soal.');
        }

        $headers = array_map(static fn (string $value): string => mb_strtolower(trim($value)), $rows[0]);
        $aliases = [
            'question' => ['question', 'pertanyaan', 'soal'],
            'A' => ['a', 'opsi a', 'pilihan a'],
            'B' => ['b', 'opsi b', 'pilihan b'],
            'C' => ['c', 'opsi c', 'pilihan c'],
            'D' => ['d', 'opsi d', 'pilihan d'],
            'answer' => ['answer', 'kunci', 'jawaban'],
            'points' => ['points', 'poin', 'nilai'],
        ];
        $columns = [];
        foreach ($aliases as $key => $names) {
            foreach ($headers as $index => $header) {
                if (in_array($header, $names, true)) {
                    $columns[$key] = $index;
                    break;
                }
            }
        }
        foreach (['question', 'A', 'B', 'C', 'D', 'answer'] as $required) {
            if (! array_key_exists($required, $columns)) {
                throw new RuntimeException('Kolom Excel wajib: Pertanyaan, A, B, C, D, Kunci. Poin bersifat opsional.');
            }
        }

        $questions = [];
        foreach (array_slice($rows, 1) as $index => $row) {
            $prompt = $this->cell($row, $columns['question']);
            if ($prompt === '') {
                continue;
            }
            $questions[] = $this->normalizeQuestion(
                $prompt,
                ['A' => $this->cell($row, $columns['A']), 'B' => $this->cell($row, $columns['B']), 'C' => $this->cell($row, $columns['C']), 'D' => $this->cell($row, $columns['D'])],
                $this->cell($row, $columns['answer']),
                isset($columns['points']) ? (int) $this->cell($row, $columns['points']) : 1,
                $index + 2,
            );
        }

        return $this->ensureQuestions($questions);
    }

    /**
     * @return array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}>
     */
    private function importPdf(string|false $path): array
    {
        if (! is_string($path)) {
            throw new RuntimeException('File PDF tidak dapat dibaca.');
        }

        $pdf = file_get_contents($path);
        if (! is_string($pdf) || ! str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('File PDF tidak valid.');
        }

        $text = '';
        $decodedBytes = 0;
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdf, $streams);
        foreach ($streams[1] ?? [] as $stream) {
            $stream = trim($stream, "\r\n");
            $decoded = @gzuncompress($stream);
            $stream = is_string($decoded) ? $decoded : $stream;
            $decodedBytes += strlen($stream);
            if ($decodedBytes > 20_000_000) {
                throw new RuntimeException('Isi PDF terlalu besar untuk diproses.');
            }
            preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj|\[(.*?)\]\s*TJ/s', $stream, $matches);
            foreach ($matches[0] ?? [] as $operator) {
                preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $operator, $strings);
                foreach ($strings[0] ?? [] as $literal) {
                    $text .= ' '.$this->decodePdfLiteral(substr($literal, 1, -1));
                }
                $text .= "\n";
            }
        }

        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        if (trim($text) === '') {
            throw new RuntimeException('Teks PDF tidak ditemukan. PDF hasil scan atau PDF dengan font encoding khusus belum didukung; gunakan PDF teks atau Excel.');
        }

        preg_match_all('/(?:^|\s)\d+[.)]\s*(.+?)\s+A[.)]\s*(.+?)\s+B[.)]\s*(.+?)\s+C[.)]\s*(.+?)\s+D[.)]\s*(.+?)\s+(?:kunci|jawaban)\s*:?\s*([A-D])/iu', $text, $matches, PREG_SET_ORDER);
        $questions = [];
        foreach ($matches as $index => $match) {
            $questions[] = $this->normalizeQuestion(trim($match[1]), ['A' => trim($match[2]), 'B' => trim($match[3]), 'C' => trim($match[4]), 'D' => trim($match[5])], $match[6], 1, $index + 1);
        }

        return $this->ensureQuestions($questions);
    }

    private function decodePdfLiteral(string $literal): string
    {
        return preg_replace_callback('/\\\\([\\\\()nrtbf]|[0-7]{1,3}|\r?\n)/', static function (array $match): string {
            return match ($match[1]) {
                'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\x0c",
                default => ctype_digit($match[1]) ? chr(octdec($match[1]) % 256) : $match[1],
            };
        }, $literal) ?? $literal;
    }

    /** @param array<string, string> $options
     * @return array{prompt: string, options: array<string, string>, answer_key: string, points: int}
     */
    private function normalizeQuestion(string $prompt, array $options, string $answer, int $points, int $row): array
    {
        $answer = strtoupper(trim($answer));
        if ($prompt === '' || in_array('', $options, true) || ! in_array($answer, ['A', 'B', 'C', 'D'], true) || $points < 1 || $points > 100) {
            throw new RuntimeException("Data soal pada baris {$row} belum lengkap. Periksa pertanyaan, pilihan A-D, kunci A-D, dan poin (1-100).");
        }

        return ['prompt' => $prompt, 'options' => $options, 'answer_key' => $answer, 'points' => $points];
    }

    /** @param array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}> $questions
     * @return array<int, array{prompt: string, options: array<string, string>, answer_key: string, points: int}>
     */
    private function ensureQuestions(array $questions): array
    {
        if ($questions === []) {
            throw new RuntimeException('Tidak ada soal yang cocok. Periksa format kolom Excel atau susunan soal PDF.');
        }
        if (count($questions) > 100) {
            throw new RuntimeException('Maksimal 100 soal per ujian.');
        }

        return $questions;
    }

    /** @param array<int, string> $row */
    private function cell(array $row, int $column): string
    {
        return trim($row[$column] ?? '');
    }

    private function columnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + ord($letter) - 64;
        }

        return $index - 1;
    }
}
