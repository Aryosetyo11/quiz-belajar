<?php

namespace App\Services;

use App\Models\ExamQuestion;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ExamQuestionImporter
{
    /**
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    public function import(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'xlsx' => $this->importSpreadsheet($file->getRealPath()),
            'csv', 'txt' => $this->importCsv($file->getRealPath()),
            'pdf' => $this->importPdf($file->getRealPath()),
            default => throw new RuntimeException('Format file belum didukung. Gunakan file Excel (.xlsx), CSV (.csv), atau dokumen PDF.'),
        };
    }

    /**
     * Mengimpor dari file CSV (koma / titik koma).
     *
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function importCsv(string|false $path): array
    {
        if (! is_string($path) || ! file_exists($path)) {
            throw new RuntimeException('File CSV tidak dapat dibaca.');
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            throw new RuntimeException('File CSV kosong.');
        }

        // Hapus UTF-8 BOM jika ada
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Deteksi delimiter (koma atau titik koma)
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        while (($row = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            // Bersihkan spasi di setiap sel
            $cleaned = array_map(fn ($val) => trim((string) $val), $row);
            // Abaikan baris kosong
            if (array_filter($cleaned) !== []) {
                $rows[] = $cleaned;
            }
        }
        fclose($stream);

        return $this->questionsFromRows($rows);
    }

    /**
     * Mengimpor dari file spreadsheet Excel (.xlsx).
     *
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function importSpreadsheet(string|false $path): array
    {
        if (! is_string($path) || ! file_exists($path)) {
            throw new RuntimeException('File Excel tidak dapat dibaca.');
        }

        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak valid atau rusak. Pastikan file disimpan dengan format .xlsx standar.');
        }

        try {
            // Cari worksheet yang tersedia (tidak kaku hanya sheet1.xml)
            $sheetName = null;
            $possibleSheets = ['xl/worksheets/sheet1.xml', 'xl/worksheets/sheet.xml', 'xl/worksheets/sheet0.xml'];
            foreach ($possibleSheets as $trySheet) {
                if ($zip->statName($trySheet) !== false) {
                    $sheetName = $trySheet;
                    break;
                }
            }

            // Jika tidak ditemukan di nama umum, cari file pertama di dalam xl/worksheets/
            if ($sheetName === null) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                        $sheetName = $name;
                        break;
                    }
                }
            }

            if ($sheetName === null) {
                throw new RuntimeException('Lembar kerja Excel (worksheet) tidak ditemukan di dalam file.');
            }

            $sheetStats = $zip->statName($sheetName);
            if (is_array($sheetStats) && ($sheetStats['size'] ?? 0) > 10_000_000) {
                throw new RuntimeException('Isi lembar Excel terlalu besar untuk diproses (maks. 10 MB).');
            }

            $sheet = $zip->getFromName($sheetName);
            if ($sheet === false) {
                throw new RuntimeException('Gagal mengekstrak lembar kerja Excel.');
            }

            // Ekstrak shared strings jika ada
            $sharedStrings = [];
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

            if ($sharedXml !== false) {
                $shared = simplexml_load_string($sharedXml);

                if ($shared instanceof SimpleXMLElement) {
                    foreach ($shared->xpath('//*[local-name()="si"]') ?: [] as $item) {
                        $textPieces = $item->xpath('.//*[local-name()="t"]') ?: [];
                        $sharedStrings[] = implode('', array_map('strval', $textPieces));
                    }
                }
            }

            $xml = simplexml_load_string($sheet);
            if (! $xml instanceof SimpleXMLElement) {
                throw new RuntimeException('Isi lembar Excel tidak dapat dipahami sebagai XML.');
            }

            $rows = [];
            foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
                $cells = [];
                $maxCol = 0;
                foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                    $reference = (string) $cell['r'];
                    preg_match('/^[A-Z]+/', $reference, $match);
                    $column = $this->columnIndex($match[0] ?? 'A');
                    $maxCol = max($maxCol, $column);

                    $type = (string) ($cell['t'] ?? '');
                    $value = (string) ($cell->xpath('./*[local-name()="v"]')[0] ?? '');

                    if ($type === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]') ?: []));
                    } elseif ($type === 'b') {
                        $value = $value === '1' ? 'TRUE' : 'FALSE';
                    }

                    $cells[$column] = trim($value);
                }

                // Normalisasi baris agar tidak ada gap kolom (indeks 0 .. maxCol berurutan)
                if ($cells !== []) {
                    $normalizedRow = [];
                    for ($c = 0; $c <= $maxCol; $c++) {
                        $normalizedRow[$c] = $cells[$c] ?? '';
                    }
                    if (array_filter($normalizedRow) !== []) {
                        $rows[] = $normalizedRow;
                    }
                }
            }

            return $this->questionsFromRows($rows);
        } finally {
            $zip->close();
        }
    }

    /**
     * Mengubah baris-baris data tabular (Excel / CSV) menjadi daftar butir soal.
     *
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function questionsFromRows(array $rows): array
    {
        if (count($rows) < 2) {
            throw new RuntimeException('File harus memiliki baris judul kolom (header) dan minimal satu baris soal.');
        }

        $headers = array_map(static fn (string $value): string => mb_strtolower(trim($value)), $rows[0]);

        $aliases = [
            'question' => ['question', 'pertanyaan', 'soal', 'soal pertanyaan', 'isi soal'],
            'A' => ['a', 'opsi a', 'pilihan a', 'opt a'],
            'B' => ['b', 'opsi b', 'pilihan b', 'opt b'],
            'C' => ['c', 'opsi c', 'pilihan c', 'opt c'],
            'D' => ['d', 'opsi d', 'pilihan d', 'opt d'],
            'answer' => ['answer', 'kunci', 'jawaban', 'kunci jawaban'],
            'points' => ['points', 'poin', 'nilai', 'bobot'],
            'type' => ['type', 'jenis', 'tipe', 'tipe soal'],
            'sample_answer' => ['pedoman', 'pedoman_jawaban', 'kunci_esai', 'rubrik', 'sample_answer'],
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

        if (! array_key_exists('question', $columns)) {
            throw new RuntimeException('Kolom "Pertanyaan" tidak ditemukan di baris pertama Excel/CSV. Periksa judul kolom Anda.');
        }

        $hasMultipleChoiceOptions = array_key_exists('A', $columns) && array_key_exists('B', $columns) && array_key_exists('C', $columns) && array_key_exists('D', $columns);

        $questions = [];
        foreach (array_slice($rows, 1) as $index => $row) {
            $prompt = $this->cell($row, $columns['question']);
            if ($prompt === '') {
                continue;
            }

            $points = isset($columns['points']) && is_numeric($this->cell($row, $columns['points']))
                ? (int) $this->cell($row, $columns['points'])
                : 10;
            $points = max(1, min(100, $points));

            // Cek apakah soal ini tipe Esai
            $explicitType = isset($columns['type']) ? mb_strtolower($this->cell($row, $columns['type'])) : '';
            $isEssay = in_array($explicitType, ['essay', 'esai', 'uraian'], true)
                || (! $hasMultipleChoiceOptions && (isset($columns['sample_answer']) || ! isset($columns['answer'])));

            if ($isEssay) {
                $sampleAnswer = isset($columns['sample_answer']) ? $this->cell($row, $columns['sample_answer']) : '';
                if ($sampleAnswer === '' && isset($columns['answer'])) {
                    $sampleAnswer = $this->cell($row, $columns['answer']);
                }

                $questions[] = [
                    'prompt' => $prompt,
                    'type' => ExamQuestion::TYPE_ESSAY,
                    'options' => null,
                    'answer_key' => null,
                    'sample_answer' => $sampleAnswer ?: null,
                    'points' => $points,
                ];
            } else {
                if (! $hasMultipleChoiceOptions) {
                    throw new RuntimeException('Soal Pilihan Ganda membutuhkan kolom: Pertanyaan, A, B, C, D, Kunci. Jika membuat soal esai, unduh template khusus Esai.');
                }

                $optA = $this->cell($row, $columns['A']);
                $optB = $this->cell($row, $columns['B']);
                $optC = $this->cell($row, $columns['C']);
                $optD = $this->cell($row, $columns['D']);
                $rawAnswer = isset($columns['answer']) ? strtoupper(trim($this->cell($row, $columns['answer']))) : '';

                // Ekstrak huruf kunci (misal jika guru menulis "A. Jakarta" -> "A")
                if (preg_match('/^[A-D]/', $rawAnswer, $ansMatch)) {
                    $answer = $ansMatch[0];
                } else {
                    $answer = 'A';
                }

                if ($optA === '' || $optB === '') {
                    throw new RuntimeException('Pilihan jawaban A dan B pada baris '.($index + 2).' tidak boleh kosong.');
                }

                $questions[] = [
                    'prompt' => $prompt,
                    'type' => ExamQuestion::TYPE_MULTIPLE_CHOICE,
                    'options' => [
                        'A' => $optA,
                        'B' => $optB,
                        'C' => $optC ?: '-',
                        'D' => $optD ?: '-',
                    ],
                    'answer_key' => $answer,
                    'sample_answer' => null,
                    'points' => $points,
                ];
            }
        }

        return $this->ensureQuestions($questions);
    }

    /**
     * Mengimpor soal dari dokumen PDF.
     *
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function importPdf(string|false $path): array
    {
        if (! is_string($path) || ! file_exists($path)) {
            throw new RuntimeException('File PDF tidak dapat dibaca.');
        }

        $pdf = file_get_contents($path);
        if (! is_string($pdf) || ! str_starts_with($pdf, '%PDF-')) {
            throw new RuntimeException('File bukan dokumen PDF yang valid.');
        }

        $text = '';
        $decodedBytes = 0;
        preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $pdf, $streams);
        foreach ($streams[1] ?? [] as $stream) {
            $stream = trim($stream, "\r\n");
            $decoded = @gzuncompress($stream);
            $stream = is_string($decoded) ? $decoded : $stream;
            $decodedBytes += strlen($stream);
            if ($decodedBytes > 25_000_000) {
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
            throw new RuntimeException('Teks PDF tidak dapat terbaca. Gunakan file Excel (.xlsx) atau CSV untuk hasil impor yang paling akurat.');
        }

        preg_match_all('/(?:^|\s)\d+[.)]\s*(.+?)\s+A[.)]\s*(.+?)\s+B[.)]\s*(.+?)\s+C[.)]\s*(.+?)\s+D[.)]\s*(.+?)\s+(?:kunci|jawaban)\s*:?\s*([A-D])/iu', $text, $matches, PREG_SET_ORDER);
        $questions = [];
        foreach ($matches as $match) {
            $questions[] = [
                'prompt' => trim($match[1]),
                'type' => ExamQuestion::TYPE_MULTIPLE_CHOICE,
                'options' => [
                    'A' => trim($match[2]),
                    'B' => trim($match[3]),
                    'C' => trim($match[4]),
                    'D' => trim($match[5]),
                ],
                'answer_key' => strtoupper(trim($match[6])),
                'sample_answer' => null,
                'points' => 10,
            ];
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

    /**
     * @param  array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>  $questions
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function ensureQuestions(array $questions): array
    {
        if ($questions === []) {
            throw new RuntimeException('Tidak ada butir soal yang terbaca dari file. Pastikan Anda menggunakan susunan kolom sesuai Template Excel yang disediakan.');
        }
        if (count($questions) > 100) {
            throw new RuntimeException('Maksimal 100 soal per sesi ujian.');
        }

        return $questions;
    }

    /** @param array<int, string> $row */
    private function cell(array $row, int $column): string
    {
        return trim((string) ($row[$column] ?? ''));
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
