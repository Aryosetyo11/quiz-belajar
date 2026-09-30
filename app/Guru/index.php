<?php
// Data dummy (sementara) — nanti bisa diganti dari database temanmu
$guru = ['nama' => 'Budi Santoso', 'nip' => '1987654321'];
$total_quiz = 8;
$quiz_aktif = 3;
$total_siswa = 42;
$pelanggaran_hari_ini = 5;

$aktivitas = [
    ['siswa' => 'Andi Pratama', 'quiz' => 'Matematika Bab 3', 'status' => 'Mengerjakan', 'pelanggaran' => 2],
    ['siswa' => 'Siti Nurhaliza', 'quiz' => 'Matematika Bab 3', 'status' => 'Selesai', 'pelanggaran' => 0],
    ['siswa' => 'Rizky Aditya', 'quiz' => 'Bahasa Indonesia', 'status' => 'Mengerjakan', 'pelanggaran' => 1],
    ['siswa' => 'Dewi Lestari', 'quiz' => 'IPA Biologi', 'status' => 'Selesai', 'pelanggaran' => 0],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Guru - LMS Quiz</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h3>🎓 LMS Quiz</h3>
        <a href="index.php" class="active">📊 Dashboard</a>
        <a href="kelola_quiz.php">📝 Kelola Quiz</a>
        <a href="monitor_ujian.php">👁️ Monitor Ujian</a>
        <a href="hasil_quiz.php">📈 Hasil Quiz</a>
        <a href="pelanggaran.php">⚠️ Pelanggaran</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">
        <div class="topbar">
            <h1>Dashboard Guru</h1>
            <div class="user">👤 <?= $guru['nama'] ?> · NIP <?= $guru['nip'] ?></div>
        </div>

        <!-- STATISTIK -->
        <div class="stats">
            <div class="stat-card">
                <h3><?= $total_quiz ?></h3>
                <p>Total Quiz</p>
            </div>
            <div class="stat-card">
                <h3><?= $quiz_aktif ?></h3>
                <p>Quiz Aktif</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_siswa ?></h3>
                <p>Siswa Terdaftar</p>
            </div>
            <div class="stat-card danger">
                <h3><?= $pelanggaran_hari_ini ?></h3>
                <p>Pelanggaran Hari Ini</p>
            </div>
        </div>

        <!-- AKSI CEPAT -->
        <div class="card">
            <h2>Aksi Cepat</h2>
            <div class="quick-actions">
                <a href="kelola_quiz.php" class="btn">+ Buat Quiz Baru</a>
                <a href="monitor_ujian.php" class="btn-outline">🔴 Mulai Monitoring</a>
                <a href="hasil_quiz.php" class="btn-outline">📥 Export Nilai</a>
            </div>
        </div>

        <!-- AKTIVITAS TERKINI -->
        <div class="card">
            <h2>Aktivitas Ujian Terkini</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Quiz</th>
                        <th>Status</th>
                        <th>Pelanggaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aktivitas as $a): ?>
                    <tr>
                        <td><?= $a['siswa'] ?></td>
                        <td><?= $a['quiz'] ?></td>
                        <td>
                            <span class="badge <?= $a['status'] == 'Selesai' ? 'badge-success' : 'badge-warning' ?>">
                                <?= $a['status'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($a['pelanggaran'] > 0): ?>
                                <span class="badge badge-danger">⚠️ <?= $a['pelanggaran'] ?>x</span>
                            <?php else: ?>
                                <span class="badge badge-success">Aman</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>