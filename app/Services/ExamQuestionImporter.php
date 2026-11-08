<?php

namespace App\Services;

use App\Models\ExamQuestion;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class ExamQuestionImporter
{
    public function __construct(private PdfExamQuestionParser $pdfParser) {}

    /**
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    public function import(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'xlsx' => $this->importSpreadsheet($file->getRealPath()),
            'csv', 'txt' => $this->importCsv($file->getRealPath()),
            'pdf' => $this->pdfParser->import($file->getRealPath()),
            default => throw new RuntimeException('Format file belum didukung. Gunakan Excel (.xlsx), CSV (.csv), TXT (.txt), atau PDF (.pdf).'),
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

        $headers = array_map(fn (string $value): string => $this->normalizeHeader($value), $rows[0]);

        $aliases = [
            'question' => ['question', 'question text', 'question_text', 'pertanyaan', 'soal', 'soal pertanyaan', 'isi soal', 'teks soal'],
            'A' => ['a', 'opsi a', 'pilihan a', 'opt a', 'option a', 'option_a', 'choice a'],
            'B' => ['b', 'opsi b', 'pilihan b', 'opt b', 'option b', 'option_b', 'choice b'],
            'C' => ['c', 'opsi c', 'pilihan c', 'opt c', 'option c', 'option_c', 'choice c'],
            'D' => ['d', 'opsi d', 'pilihan d', 'opt d', 'option d', 'option_d', 'choice d'],
            'answer' => ['answer', 'answer key', 'answer_key', 'correct answer', 'kunci', 'jawaban', 'kunci jawaban'],
            'points' => ['points', 'poin', 'nilai', 'bobot'],
            'type' => ['type', 'jenis', 'tipe', 'tipe soal'],
            'sample_answer' => ['pedoman', 'pedoman_jawaban', 'kunci_esai', 'rubrik', 'sample_answer'],
        ];

        $columns = [];
        foreach ($aliases as $key => $names) {
            $normalizedAliases = array_map(fn (string $name): string => $this->normalizeHeader($name), $names);
            foreach ($headers as $index => $header) {
                if (in_array($header, $normalizedAliases, true)) {
                    $columns[$key] = $index;
                    break;
                }
            }
        }

        if (! array_key_exists('question', $columns)) {
            throw new RuntimeException('Kolom "Pertanyaan" tidak ditemukan di baris pertama Excel/CSV. Periksa judul kolom Anda.');
        }

        $hasMultipleChoiceOptions = array_key_exists('A', $columns) && array_key_exists('B', $columns);

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
                    throw new RuntimeException('Soal pilihan ganda membutuhkan kolom Pertanyaan, A, B, dan Kunci. Kolom C dan D boleh kosong; soal esai dapat memakai kolom Pedoman Jawaban.');
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

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim($header));

        return trim(preg_replace('/[^\pL\pN]+/u', '_', $header) ?? $header, '_');
    }

    /**
     * @param  array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>  $questions
     * @return array<int, array{prompt: string, type: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}>
     */
    private function ensureQuestions(array $questions): array
    {
        if ($questions === []) {
            throw new RuntimeException('Tidak ada soal yang berhasil dibaca. Pastikan setiap soal memiliki nomor, teks soal, dan susunan kolom atau label yang dikenali.');
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
