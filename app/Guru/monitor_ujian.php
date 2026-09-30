<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitor Ujian - LMS Quiz</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <aside class="sidebar">
        <h3>🎓 LMS Quiz</h3>
        <a href="index.php">📊 Dashboard</a>
        <a href="kelola_quiz.php">📝 Kelola Quiz</a>
        <a href="monitor_ujian.php" class="active">👁️ Monitor Ujian</a>
        <a href="hasil_quiz.php">📈 Hasil Quiz</a>
        <a href="pelanggaran.php">⚠️ Pelanggaran</a>
    </aside>

    <main class="main">
        <div class="topbar">
            <h1>👁️ Monitoring Ujian Live</h1>
            <div class="user">
                <span class="live-dot"></span> Live · 3 siswa aktif
            </div>
        </div>

        <div class="card">
            <h2>Status Siswa Saat Ini</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Quiz</th>
                        <th>Waktu Mulai</th>
                        <th>Progress</th>
                        <th>Pelanggaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Andi Pratama</td>
                        <td>Matematika Bab 3</td>
                        <td>10:15</td>
                        <td><div class="progress"><div style="width:60%">60%</div></div></td>
                        <td><span class="badge badge-danger">⚠️ Tab switch 2x</span></td>
                        <td><a href="#" class="btn-sm btn-danger">Tegur</a></td>
                    </tr>
                    <tr>
                        <td>Rizky Aditya</td>
                        <td>Bahasa Indonesia</td>
                        <td>10:20</td>
                        <td><div class="progress"><div style="width:35%">35%</div></div></td>
                        <td><span class="badge badge-warning">⚠️ 1x</span></td>
                        <td><a href="#" class="btn-sm btn-danger">Tegur</a></td>
                    </tr>
                    <tr>
                        <td>Dewi Lestari</td>
                        <td>IPA Biologi</td>
                        <td>10:22</td>
                        <td><div class="progress"><div style="width:80%">80%</div></div></td>
                        <td><span class="badge badge-success">Aman</span></td>
                        <td>—</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>