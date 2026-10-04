<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Portal RuangUji untuk pengguna Yayasan NC Learning Center.')">
    <title>@yield('title', 'Portal RuangUji')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-5 py-8 sm:px-6 sm:py-12">
        <div class="w-full">
            @yield('content')
        </div>
    </main>
</body>
</html>
