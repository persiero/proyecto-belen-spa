<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Acceso Administrativo | Belen</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('css/belen-custom.css') }}">
</head>

<body style="background-color: var(--belen-dark, #212124) !important; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0;">
    
    <div id="app" class="w-100">
        
        <main>
            @yield('content')
        </main>
        
    </div>
</body>
</html>
