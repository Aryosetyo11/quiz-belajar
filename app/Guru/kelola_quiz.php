<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Quiz - LMS Quiz</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <aside class="sidebar">
        <h3>🎓 LMS Quiz</h3>
        <a href="index.php">📊 Dashboard</a>
        <a href="kelola_quiz.php" class="active">📝 Kelola Quiz</a>
        <a href="monitor_ujian.php">👁️ Monitor Ujian</a>
        <a href="hasil_quiz.php">📈 Hasil Quiz</a>
        <a href="pelanggaran.php">⚠️ Pelanggaran</a>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>Kelola Quiz</h1>
            <button class="btn">+ Buat Quiz Baru</button>
        </div>

        <div class="card">
            <h2>Daftar Quiz</h2>
            <table>
                <thead>
                    <tr>
                        <th>Judul Quiz</th>
                        <th>Jumlah Soal</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Matematika Bab 3</td>
                        <td>20 soal</td>
                        <td>45 menit</td>
                        <td><span class="badge badge-success">Aktif</span></td>
                        <td>
                            <a href="#" class="btn-sm">Edit</a>
                            <a href="#" class="btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Bahasa Indonesia - Puisi</td>
                        <td>15 soal</td>
                        <td>30 menit</td>
                        <td><span class="badge badge-warning">Draft</span></td>
                        <td>
                            <a href="#" class="btn-sm">Edit</a>
                            <a href="#" class="btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>