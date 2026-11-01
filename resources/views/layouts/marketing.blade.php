<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RuangUji menghubungkan materi kelas, ujian digital, dan hasil belajar untuk siswa, guru, sekolah, dan Yayasan NC Learning Center.">
    <title>RuangUji | Portal Belajar dan Ujian Digital</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased selection:bg-indigo-600 selection:text-white">
    @include('partials.marketing-header')

    @yield('content')

    @include('partials.marketing-footer')
</body>
</html>
