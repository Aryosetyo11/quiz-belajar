<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RuangUji - Sistem Informasi Ujian Digital Berbasis Web Yayasan NC Learning Center dengan Proteksi Real-Time dan Manajemen Akses Terstruktur.">
    <title>RuangUji | Sistem Ujian Digital Yayasan NC Learning Center</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased selection:bg-indigo-600 selection:text-white">
    @include('partials.marketing-header')

    @yield('content')

    @include('partials.portal-modal')
    @include('partials.marketing-footer')
</body>
</html>
