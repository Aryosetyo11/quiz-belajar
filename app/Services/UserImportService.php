<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use SimpleXMLElement;
use ZipArchive;

class UserImportService
{
    /**
     * @return array{created: int}
     *
     * @throws ValidationException
     */
    public function import(UploadedFile $file, int $schoolId): array
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(300);
        }

        $rows = $this->readRows($file);

        if (count($rows) > 300) {
            throw ValidationException::withMessages(['file' => 'Maksimal 300 akun dalam satu kali impor.']);
        }

        $validRows = [];
        $errors = [];
        $seenEmails = [];
        $seenNisns = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $role = $this->normalizeRole((string) ($row['role'] ?? ''));
            $data = [
                'role' => $role,
                'name' => trim((string) ($row['name'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')) ?: null,
                'nisn' => preg_replace('/\s+/', '', (string) ($row['nisn'] ?? '')) ?: null,
                'password' => (string) ($row['password'] ?? ''),
            ];
            $validator = Validator::make($data, [
                'role' => ['required', 'in:guru,siswa'],
                'name' => ['required', 'string', 'max:255'],
                'email' => [$role === User::ROLE_TEACHER ? 'required' : 'nullable', 'email', 'max:255', 'unique:users,email'],
                'nisn' => [$role === User::ROLE_STUDENT ? 'required' : 'nullable', 'digits:10', 'unique:users,nisn'],
                'password' => ['required', Password::min(12)->mixedCase()->letters()->numbers()],
            ], [
                'role.in' => 'role harus guru atau siswa.',
                'name.required' => 'nama wajib diisi.',
                'email.required' => 'email wajib diisi untuk guru.',
                'email.email' => 'format email tidak valid.',
                'email.unique' => 'email sudah digunakan.',
                'nisn.required' => 'NISN wajib diisi untuk siswa.',
                'nisn.digits' => 'NISN harus tepat 10 digit.',
                'nisn.unique' => 'NISN sudah digunakan.',
                'password.required' => 'password wajib diisi.',
                'password.min' => 'password minimal 12 karakter.',
            ]);

            foreach ($validator->errors()->all() as $message) {
                $errors[] = "Baris {$line}: {$message}";
            }

            if ($data['email'] !== null) {
                $emailKey = mb_strtolower($data['email']);
                if (isset($seenEmails[$emailKey])) {
                    $errors[] = "Baris {$line}: email duplikat dengan baris {$seenEmails[$emailKey]}.";
                } else {
                    $seenEmails[$emailKey] = $line;
                }
            }

            if ($data['nisn'] !== null) {
                if (isset($seenNisns[$data['nisn']])) {
                    $errors[] = "Baris {$line}: NISN duplikat dengan baris {$seenNisns[$data['nisn']]}.";
                } else {
                    $seenNisns[$data['nisn']] = $line;
                }
            }

            $validRows[] = $data;
        }

        if ($validRows === []) {
            throw ValidationException::withMessages(['file' => 'File belum berisi baris akun.']);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => implode(' ', array_slice($errors, 0, 20))]);
        }

        DB::transaction(function () use ($validRows, $schoolId): void {
            foreach ($validRows as $row) {
                User::query()->create([
                    'name' => $row['name'],
                    'role' => $row['role'],
                    'school_id' => $schoolId,
                    'email' => $row['role'] === User::ROLE_TEACHER ? $row['email'] : null,
                    'nisn' => $row['role'] === User::ROLE_STUDENT ? $row['nisn'] : null,
                    'password' => $row['password'],
                ]);
            }
        });

        return ['created' => count($validRows)];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readRows(UploadedFile $file): array
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'csv' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages(['file' => 'Gunakan file Excel .xlsx atau CSV .csv.']),
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readCsv(string|false $path): array
    {
        if (! is_string($path) || ! is_file($path)) {
            throw ValidationException::withMessages(['file' => 'File CSV tidak dapat dibaca.']);
        }

        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            throw ValidationException::withMessages(['file' => 'File CSV kosong.']);
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        $rows = [];

        while (($row = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            $rows[] = array_map(static fn ($value): string => trim((string) $value), $row);
        }

        fclose($stream);

        return $this->mapRows($rows);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function readXlsx(string|false $path): array
    {
        if (! is_string($path) || ! is_file($path) || ! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages(['file' => 'File Excel tidak dapat dibaca. Pastikan ekstensi PHP ZIP aktif.']);
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => 'File Excel rusak atau bukan format .xlsx standar.']);
        }

        try {
            $sheetName = null;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);
                if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $sheetName = $name;
                    break;
                }
            }

            if ($sheetName === null) {
                throw ValidationException::withMessages(['file' => 'Sheet pertama tidak ditemukan di file Excel.']);
            }

            $sheetXml = $zip->getFromName($sheetName);
            $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
            if ($sheetXml === false || strlen($sheetXml) > 10_000_000) {
                throw ValidationException::withMessages(['file' => 'Isi sheet Excel tidak valid atau melebihi 10 MB.']);
            }

            $sharedStrings = [];
            if ($sharedXml !== false) {
                $shared = simplexml_load_string($sharedXml);
                if ($shared instanceof SimpleXMLElement) {
                    foreach ($shared->xpath('//*[local-name()="si"]') ?: [] as $item) {
                        $pieces = $item->xpath('.//*[local-name()="t"]') ?: [];
                        $sharedStrings[] = implode('', array_map('strval', $pieces));
                    }
                }
            }

            $sheet = simplexml_load_string($sheetXml);
            if (! $sheet instanceof SimpleXMLElement) {
                throw ValidationException::withMessages(['file' => 'Struktur sheet Excel tidak dapat dibaca.']);
            }

            $rows = [];
            foreach ($sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
                $cells = [];
                $maxColumn = -1;
                foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                    preg_match('/^[A-Z]+/', (string) $cell['r'], $matches);
                    $column = $this->columnIndex($matches[0] ?? 'A');
                    $type = (string) ($cell['t'] ?? '');
                    $value = (string) ($cell->xpath('./*[local-name()="v"]')[0] ?? '');

                    if ($type === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = implode('', array_map('strval', $cell->xpath('.//*[local-name()="t"]') ?: []));
                    }

                    $cells[$column] = trim($value);
                    $maxColumn = max($maxColumn, $column);
                }

                if ($cells !== []) {
                    $rows[] = array_map(static fn (int $column): string => $cells[$column] ?? '', range(0, $maxColumn));
                }
            }

            return $this->mapRows($rows);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array<string, string>>
     */
    private function mapRows(array $rows): array
    {
        if (count($rows) < 2) {
            throw ValidationException::withMessages(['file' => 'File harus memiliki header dan minimal satu baris akun.']);
        }

        $headers = array_map(fn (string $header): string => $this->normalizeHeader($header), array_shift($rows));
        $aliases = [
            'role' => ['role', 'peran', 'jenisakun', 'tipeakun'],
            'name' => ['nama', 'namalengkap', 'name', 'fullname'],
            'email' => ['email', 'emaillogin'],
            'nisn' => ['nisn'],
            'password' => ['password', 'katasandi', 'sandi'],
        ];
        $columns = [];
        foreach ($aliases as $field => $options) {
            foreach ($headers as $index => $header) {
                if (in_array($header, $options, true)) {
                    $columns[$field] = $index;
                    break;
                }
            }
        }

        foreach (['role', 'name', 'email', 'nisn', 'password'] as $requiredHeader) {
            if (! array_key_exists($requiredHeader, $columns)) {
                throw ValidationException::withMessages(['file' => 'Header wajib: role, nama, email, nisn, password. Pastikan nama kolom sesuai template.']);
            }
        }

        $mapped = [];
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $field => $column) {
                $values[$field] = (string) ($row[$column] ?? '');
            }

            if (count(array_filter($values, static fn (string $value): bool => trim($value) !== '')) > 0) {
                $mapped[] = $values;
            }
        }

        return $mapped;
    }

    private function normalizeRole(string $role): string
    {
        return match (mb_strtolower(trim($role))) {
            'guru', 'teacher', 'pengajar' => User::ROLE_TEACHER,
            'siswa', 'student', 'murid' => User::ROLE_STUDENT,
            default => mb_strtolower(trim($role)),
        };
    }

    private function normalizeHeader(string $header): string
    {
        $header = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header));

        return preg_replace('/[^a-z0-9]/', '', $header) ?? $header;
    }

    private function columnIndex(string $letters): int
    {
        $column = 0;
        foreach (str_split($letters) as $letter) {
            $column = ($column * 26) + (ord($letter) - 64);
        }

        return max(0, $column - 1);
    }
}
