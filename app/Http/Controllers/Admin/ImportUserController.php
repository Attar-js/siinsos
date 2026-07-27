<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportUserController extends Controller
{
    /**
     * Konfigurasi impor per role. Menentukan kolom template, kolom wajib,
     * kunci duplikat, dan sumber password default.
     */
    private function config(): array
    {
        return [
            'mahasiswa' => [
                'role' => 'mahasiswa',
                'user_type' => 'user',
                'label' => 'Mahasiswa',
                'headers' => ['nim', 'nama', 'email', 'program_studi'],
                'required' => ['nim', 'nama'],
                'unique_by' => 'nim',
                'password_from' => 'nim',
                'example' => [
                    ['10221099', 'Budi Santoso', '', 'Informatika'],
                    ['10221100', 'Siti Aminah', 'siti@student.itk.ac.id', 'Sistem Informasi'],
                ],
                'note' => 'Login memakai NIM. Email & username otomatis dari NIM bila kosong. Password default = NIM.',
            ],
            'dosen' => [
                'role' => 'dosen',
                'user_type' => 'user',
                'label' => 'Dosen',
                'headers' => ['nip', 'nama', 'email', 'username', 'phone_number'],
                'required' => ['nip', 'nama', 'email'],
                'unique_by' => 'nip',
                'password_from' => 'nip',
                'example' => [
                    ['199208012019031010', 'Dr. Ahmad Fauzi, S.T., M.T.', 'ahmad@itk.ac.id', '', '081234567890'],
                    ['199208012019031011', 'Dr. Sarah, M.Kom.', 'sarah@itk.ac.id', 'sarah', ''],
                ],
                'note' => 'Login memakai Email. Username otomatis dari NIP bila kosong. Password default = NIP.',
            ],
        ];
    }

    private function resolve(string $role): array
    {
        $config = $this->config();
        abort_unless(isset($config[$role]), 404);

        return $config[$role] + ['key' => $role];
    }

    /**
     * Tampilkan halaman impor untuk sebuah role, beserta tabel pengguna
     * terdaftar yang bisa dicari untuk verifikasi cepat.
     */
    public function show(Request $request, string $role)
    {
        $cfg = $this->resolve($role);

        $search = trim((string) $request->get('q', ''));

        $query = User::where('role', $cfg['role']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $totalTerdaftar = User::where('role', $cfg['role'])->count();

        return view('admin.import.index', [
            'cfg' => $cfg,
            'users' => $users,
            'search' => $search,
            'totalTerdaftar' => $totalTerdaftar,
        ]);
    }

    /**
     * Unduh template Excel (.xlsx) sesuai role.
     *
     * Memakai .xlsx (bukan CSV) agar kolom selalu rapi di Excel tanpa
     * terpengaruh setelan pemisah daftar (koma/titik-koma) di komputer user.
     */
    public function template(string $role)
    {
        $cfg = $this->resolve($role);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template');

        // Header pada baris 1.
        $colNo = 1;
        foreach ($cfg['headers'] as $header) {
            $sheet->setCellValueExplicit([$colNo, 1], $header, DataType::TYPE_STRING);
            $sheet->getColumnDimensionByColumn($colNo)->setWidth(24);
            $colNo++;
        }
        $sheet->getStyle([1, 1, count($cfg['headers']), 1])->getFont()->setBold(true);

        // Semua kolom sebagai teks agar NIM/NIP panjang tidak berubah jadi notasi ilmiah.
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cfg['headers']));
        $sheet->getStyle('A:' . $lastCol)->getNumberFormat()->setFormatCode('@');

        // Contoh data mulai baris 2.
        $rowNo = 2;
        foreach ($cfg['example'] as $example) {
            $colNo = 1;
            foreach ($example as $value) {
                $sheet->setCellValueExplicit([$colNo, $rowNo], (string) $value, DataType::TYPE_STRING);
                $colNo++;
            }
            $rowNo++;
        }

        $filename = 'template_import_' . str_replace('-', '_', $role) . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Proses file impor.
     */
    public function import(Request $request, string $role)
    {
        $cfg = $this->resolve($role);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:20480'],
        ], [
            'file.required' => 'Silakan pilih file terlebih dahulu.',
            'file.mimes' => 'Format file harus CSV atau Excel (xlsx/xls).',
            'file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        try {
            $rows = $this->readRows($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return back()->with('error', 'File kosong atau hanya berisi baris judul kolom.');
        }

        // Petakan header (baris pertama) -> indeks kolom, case-insensitive.
        $header = array_map(function ($h) {
            $h = strtolower(trim((string) $h));
            // Buang BOM jika masih menempel di header pertama.
            return preg_replace('/^\x{FEFF}/u', '', $h) ?? $h;
        }, $rows[0]);
        $colIndex = [];
        foreach ($cfg['headers'] as $name) {
            $idx = array_search($name, $header, true);
            if ($idx !== false) {
                $colIndex[$name] = $idx;
            }
        }

        foreach ($cfg['required'] as $reqCol) {
            if (!isset($colIndex[$reqCol])) {
                return back()->with('error', "Kolom wajib '{$reqCol}' tidak ditemukan di file. Gunakan template yang disediakan.");
            }
        }

        $dataRows = array_slice($rows, 1);
        $parsed = [];
        $errors = [];
        $seen = ['nim' => [], 'nip' => [], 'email' => [], 'username' => []];

        foreach ($dataRows as $i => $row) {
            $lineNo = $i + 2; // +2: 1 header + 1-based

            $get = function ($col) use ($row, $colIndex) {
                if (!isset($colIndex[$col])) {
                    return null;
                }
                $val = $row[$colIndex[$col]] ?? null;
                return $val === null ? null : trim((string) $val);
            };

            // Lewati baris kosong sepenuhnya.
            $nonEmpty = array_filter($cfg['headers'], fn ($c) => filled($get($c)));
            if (empty($nonEmpty)) {
                continue;
            }

            $attrs = $this->buildAttributes($cfg, $get);

            // Validasi kolom wajib.
            $missing = [];
            foreach ($cfg['required'] as $reqCol) {
                if (blank($get($reqCol))) {
                    $missing[] = $reqCol;
                }
            }
            if ($missing) {
                $errors[] = "Baris {$lineNo}: kolom wajib kosong (" . implode(', ', $missing) . ').';
                continue;
            }

            // Duplikat di dalam file.
            $dupInFile = null;
            foreach (['nim', 'nip', 'email', 'username'] as $uc) {
                if (!empty($attrs[$uc]) && isset($seen[$uc][strtolower($attrs[$uc])])) {
                    $dupInFile = $uc;
                    break;
                }
            }
            if ($dupInFile) {
                $errors[] = "Baris {$lineNo}: {$dupInFile} '{$attrs[$dupInFile]}' duplikat di dalam file (dilewati).";
                continue;
            }

            foreach (['nim', 'nip', 'email', 'username'] as $uc) {
                if (!empty($attrs[$uc])) {
                    $seen[$uc][strtolower($attrs[$uc])] = true;
                }
            }

            $parsed[] = ['line' => $lineNo, 'attrs' => $attrs];
        }

        // Deteksi duplikat terhadap database (email, username, nim, nip).
        $existing = $this->existingUniques($parsed);

        $toInsert = [];
        $duplicates = [];
        $now = now();

        foreach ($parsed as $item) {
            $attrs = $item['attrs'];
            $dupCol = null;
            foreach (['nim', 'nip', 'email', 'username'] as $uc) {
                if (!empty($attrs[$uc]) && isset($existing[$uc][strtolower($attrs[$uc])])) {
                    $dupCol = $uc;
                    break;
                }
            }

            if ($dupCol) {
                $duplicates[] = "Baris {$item['line']}: {$dupCol} '{$attrs[$dupCol]}' sudah ada (dilewati).";
                continue;
            }

            $attrs['created_at'] = $now;
            $attrs['updated_at'] = $now;
            $toInsert[] = $attrs;
        }

        $created = 0;
        foreach (array_chunk($toInsert, 500) as $chunk) {
            User::insert($chunk);
            $created += count($chunk);
        }

        $summary = [
            'created' => $created,
            'duplicates' => $duplicates,
            'errors' => $errors,
            'total' => count($dataRows),
        ];

        return back()->with('import_summary', $summary)
            ->with('success', "Impor selesai: {$created} akun {$cfg['label']} berhasil dibuat.");
    }

    /**
     * Bangun atribut user dari sebuah baris sesuai konfigurasi role.
     */
    private function buildAttributes(array $cfg, callable $get): array
    {
        $nama = (string) $get('nama');
        $parts = preg_split('/\s+/', trim($nama), 2);

        $nim = $get('nim');
        $nip = $get('nip');
        $email = $get('email');
        $username = $get('username');

        // Default email untuk mahasiswa dari NIM.
        if (blank($email) && $cfg['role'] === 'mahasiswa' && filled($nim)) {
            $email = $nim . '@student.itk.ac.id';
        }

        // Default username.
        if (blank($username)) {
            if ($cfg['role'] === 'mahasiswa' && filled($nim)) {
                $username = $nim;
            } elseif ($cfg['role'] === 'dosen' && filled($nip)) {
                $username = $nip;
            }
        }

        // Sumber password default.
        $passwordSource = match ($cfg['password_from']) {
            'nim' => $nim,
            'nip' => $nip,
            'username' => $username,
            default => null,
        };
        $passwordSource = filled($passwordSource) ? $passwordSource : 'password123';

        return [
            'name' => $nama,
            'first_name' => $parts[0] ?? $nama,
            'last_name' => $parts[1] ?? '',
            'email' => $email ?: null,
            'username' => $username ?: null,
            'nim' => $cfg['role'] === 'mahasiswa' ? ($nim ?: null) : null,
            'nip' => $cfg['role'] === 'dosen' ? ($nip ?: null) : null,
            'program_studi' => $get('program_studi') ?: null,
            'phone_number' => $get('phone_number') ?: null,
            'role' => $cfg['role'],
            'user_type' => $cfg['user_type'],
            'status' => 'active',
            'password' => Hash::make($passwordSource),
            'email_verified_at' => now(),
        ];
    }

    /**
     * Ambil nilai unik yang sudah ada di DB untuk baris yang akan diimpor.
     */
    private function existingUniques(array $parsed): array
    {
        $collect = fn ($col) => collect($parsed)
            ->pluck("attrs.{$col}")
            ->filter()
            ->map(fn ($v) => (string) $v)
            ->unique()
            ->values()
            ->all();

        $result = ['nim' => [], 'nip' => [], 'email' => [], 'username' => []];

        foreach (['nim', 'nip', 'email', 'username'] as $col) {
            $values = $collect($col);
            if (empty($values)) {
                continue;
            }
            foreach (array_chunk($values, 1000) as $chunk) {
                User::whereIn($col, $chunk)->pluck($col)->each(function ($v) use (&$result, $col) {
                    $result[$col][strtolower((string) $v)] = true;
                });
            }
        }

        return $result;
    }

    /**
     * Baca file CSV/Excel menjadi array baris.
     */
    private function readRows(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // CSV: baca dengan fgetcsv (lebih andal untuk tanda kutip & pemisah).
        if (in_array($ext, ['csv', 'txt'], true)) {
            $rows = $this->readCsvFile($path);
        } else {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }

        return $this->normalizeRows($rows);
    }

    /**
     * Baca CSV dengan deteksi pemisah (koma / titik-koma) dan BOM UTF-8.
     */
    private function readCsvFile(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException('Tidak dapat membaca isi file CSV.');
        }

        // Hapus BOM UTF-8 jika ada.
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $delimiter = $this->detectDelimiter($content);
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $rows = [];
        while (($data = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    private function detectDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: '';
        $commas = substr_count($firstLine, ',');
        $semicolons = substr_count($firstLine, ';');

        return $semicolons > $commas ? ';' : ',';
    }

    /**
     * Ubah nilai sel menjadi string polos (tangani RichText / angka Excel).
     */
    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            return trim($value->getPlainText());
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        // Hindari notasi ilmiah untuk NIM/NIP panjang.
        if (is_float($value) || is_int($value)) {
            if (is_float($value) && floor($value) != $value) {
                return trim((string) $value);
            }

            return trim(sprintf('%.0f', $value));
        }

        return trim((string) $value);
    }

    /**
     * Perbaiki baris yang "kolaps" jadi satu kolom.
     *
     * Sering terjadi saat CSV dibuka di Excel (semua teks numpuk di kolom A)
     * lalu disimpan ulang sebagai .xlsx / .csv. Sel pertama berisi seluruh
     * baris CSV, sementara kolom nama/email tampak kosong.
     */
    private function normalizeRows(array $rows): array
    {
        return array_map(function ($row) {
            if (!is_array($row)) {
                return [];
            }

            // Rapikan indeks & ubah semua sel jadi string.
            $row = array_values($row);
            $cells = array_map(fn ($v) => $this->cellToString($v), $row);

            $nonEmpty = array_values(array_filter($cells, fn ($v) => $v !== ''));
            $first = $cells[0] ?? '';

            // Satu sel berisi seluruh baris CSV (koma atau titik-koma).
            $looksCollapsed = count($nonEmpty) <= 1
                && $first !== ''
                && (str_contains($first, ',') || str_contains($first, ';'));

            if ($looksCollapsed) {
                $delimiter = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
                return array_map(
                    fn ($v) => trim((string) $v),
                    str_getcsv($first, $delimiter, '"', '\\')
                );
            }

            return $cells;
        }, $rows);
    }
}
