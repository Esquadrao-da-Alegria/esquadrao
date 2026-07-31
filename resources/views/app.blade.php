<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tema claro fixo --}}
    <style>
        html {
            background-color: oklch(1 0 0);
            color-scheme: light;
        }
    </style>

    <title inertia>{{ config('app.name', 'Esquadrão da Alegria') }}</title>

    <!-- Ícones e Favicon Esquadrão da Alegria -->
    <link rel="icon" type="image/png" href="/assets/images/logo-colorida.png">
    <link rel="shortcut icon" type="image/png" href="/assets/images/logo-colorida.png">
    <link rel="apple-touch-icon" href="/assets/images/logo-colorida.png">
    <link rel="manifest" href="/manifest.json">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>

    <!-- Bootstrap -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->

    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&display=swap" rel="stylesheet">


    @routes
    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead

</head>

<body class="font-sans antialiased">
    @inertia

    <!-- JS do Bootstrap Bundle (com Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
