<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DentalOS') }}</title>

    <!-- SEO & Metadata -->
    <meta name="description" content="Next-Gen Dental Practice Management System">
    <meta property="og:title" content="DentalOS">
    <meta property="og:description" content="High-performance dental practice management.">
    <meta property="og:type" content="website">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Initial State -->
    <script>
        window.__INITIAL_STATE__ = {
            csrfToken: "{{ csrf_token() }}",
            appName: "{{ config('app.name') }}",
            environment: "{{ app()->environment() }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-slate-950">
    <div id="app"></div>
</body>
</html>
