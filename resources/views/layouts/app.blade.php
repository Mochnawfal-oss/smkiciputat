<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Website resmi SMK Islamiyah Ciputat. Informasi sekolah, program keahlian, tenaga kependidikan, BKK, dan penerimaan siswa baru.">

    <title>@yield('title', 'SMK Islamiyah Ciputat')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    @include('components.footer')
</body>
</html>
