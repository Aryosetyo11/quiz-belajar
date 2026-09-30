# Portal Guru dan Siswa

## Portal yang tersedia

- Guru masuk melalui `/login/guru` memakai email dan kata sandi.
- Siswa masuk melalui `/login/siswa` memakai NISN 10 digit dan kata sandi.
- `/guru` dan `/siswa` hanya dapat dibuka oleh pengguna dengan role yang cocok.
- Registrasi publik tidak tersedia. Administrator yayasan membuat dan membagikan akun.
- Dashboard sudah menjadi titik awal portal. Fitur bank soal, sesi ujian, dan pengerjaan kuis belum termasuk implementasi ini.

## Konfigurasi MySQL

Atur koneksi pada `.env` sesuai server MySQL:

```dotenv
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quiz_belajar
DB_USERNAME=quiz_belajar_app
DB_PASSWORD=isi-password-database-yang-kuat
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
```

Gunakan HTTPS saat `SESSION_SECURE_COOKIE=true`. Buat database dan pengguna MySQL dengan hak minimum yang diperlukan, lalu jalankan:

```bash
php artisan key:generate
php artisan migrate --force
```

Migrasi Laravel ada di `database/migrations/2026_09_30_103012_add_portal_identity_to_users_table.php`. Alternatif skrip khusus MySQL tersedia di `docs/portal-mysql-migration.sql`; gunakan salah satu cara migrasi, jangan keduanya. Skrip SQL tersebut dijalankan setelah tabel `users` bawaan Laravel dibuat.

Kolom `email` dibuat nullable agar akun siswa cukup memakai NISN. Email tetap unik untuk guru, NISN unik dan berukuran tetap 10 karakter, dan role MySQL dibatasi pada `guru` atau `siswa`. Baris akun lama diberi role default `siswa`; akun lama tanpa NISN belum dapat masuk sebagai siswa sampai administrator mengisi NISN.

## Membuat akun dengan password yang di-hash

Buat seeder khusus internal dengan `php artisan make:seeder PortalAccountSeeder --no-interaction`, lalu gunakan Eloquent dan hash bawaan Laravel. Simpan password sementara di secret manager atau `.env` lokal yang tidak di-commit (misalnya `PORTAL_TEACHER_PASSWORD` dan `PORTAL_STUDENT_PASSWORD`) dan validasi nilainya sebelum membuat akun:

```php
use App\Models\User;

$teacherPassword = env('PORTAL_TEACHER_PASSWORD');

if (! is_string($teacherPassword) || strlen($teacherPassword) < 12) {
    throw new RuntimeException('PORTAL_TEACHER_PASSWORD harus minimal 12 karakter.');
}

User::create([
    'name' => 'Nama Guru',
    'email' => 'guru001@yayasan.nclearning',
    'role' => User::ROLE_TEACHER,
    'password' => $teacherPassword,
]);

$studentPassword = env('PORTAL_STUDENT_PASSWORD');

if (! is_string($studentPassword) || strlen($studentPassword) < 12) {
    throw new RuntimeException('PORTAL_STUDENT_PASSWORD harus minimal 12 karakter.');
}

User::create([
    'name' => 'Nama Siswa',
    'email' => null,
    'nisn' => '0012345678',
    'role' => User::ROLE_STUDENT,
    'password' => $studentPassword,
]);
```

Model `User` memiliki cast `hashed`, sehingga nilai password biasa yang diberikan ketika membuat/memperbarui model otomatis di-hash Laravel (bcrypt secara default). Hash itulah yang tersimpan dalam kolom `password`; jangan melakukan hash dua kali secara manual. Gunakan password unik minimal 12 karakter dan bagikan akun lewat kanal aman. Untuk akun produksi, buat seeder provisioning privat dan jalankan hanya secara sengaja.

## Akun dummy untuk pengembangan

Di lingkungan `local` atau `testing`, jalankan `php artisan migrate` lalu `php artisan db:seed` untuk membuat akun berikut jika belum ada. Seeder akan menolak dijalankan di environment lain dan tidak mengubah akun yang sudah memakai email/NISN tersebut. Jangan gunakan akun ini di production.

| Peran | Login | Password |
|---|---|---|
| Guru | `guru001@yayasan.nclearning` | `GuruDemo!2026` |
| Siswa 1 | NISN `0000000001` | `SiswaDemo01!2026` |
| Siswa 2 | NISN `0000000002` | `SiswaDemo02!2026` |

Setiap password tetap di-hash oleh model `User` sebelum disimpan.

## Perlindungan yang diterapkan

- Password tidak disimpan sebagai teks biasa. Verifikasi menggunakan `Hash::check` dan respons gagal tidak membedakan akun tidak ditemukan dari password salah.
- Ada batas 30 permintaan login per menit per alamat IP dan maksimal 5 kegagalan per role/identitas dalam 15 menit.
- Form memakai middleware web Laravel dan token CSRF. Sesi diregenerasi setelah login dan diinvalidasi saat logout.
- Cookie sesi dikonfigurasi `HttpOnly` dan `SameSite=Lax` oleh konfigurasi Laravel; contoh konfigurasi mengaktifkan enkripsi sesi dan cookie `Secure` pada HTTPS.
- Tidak ada pendaftaran mandiri atau reset password publik. Administrator harus membuat akun dan mengatur ulang password melalui proses internal yang tepercaya.
- Jangan menaruh password plaintext pada tiket, log, SQL, commit, atau chat. Gunakan akun database aplikasi dengan hak minimum dan cadangkan database secara terenkripsi.
