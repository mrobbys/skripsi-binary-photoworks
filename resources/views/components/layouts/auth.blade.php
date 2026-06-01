<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    {{-- TODO: buat logic ketika mode dark pakai yang 'binary-logo-white.png', jika tidak pakai 'binary-logo-black.png' --}}
    <link rel="icon" href="{{ asset('assets/binary-logo/binary-logo-white.png') }}" type="image/png" />

    <title>{{ $title ?? '' }} | {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    {{ $styles ?? '' }}
</head>

<body data-feature="{{ $featureName ?? '' }}" data-page="{{ $pageName ?? '' }}">
    <main class="h-dvh w-full mx-auto max-w-7xl flex items-center justify-center p-4">
        {{ $content ?? '' }}
    </main>

    {{-- trigger alert dan toast berdasarkan session  --}}
    <x-scripts.alert-toast />
    {{-- for javascript --}}
    {{ $scripts ?? '' }}
</body>

</html>
