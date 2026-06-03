<!DOCTYPE html>
<html lang="id">

<head>
  <x-layouts.components.head />
  <title>
    @isset($title)
      {{ $title }} |
    @endisset
    {{ config('app.name', 'Laravel') }}
  </title>
  {{ $heads ?? '' }}
</head>

<body data-module="{{ $jsModule ?? '' }}">
  <main class="h-dvh w-full mx-auto max-w-7xl flex items-center justify-center p-4">
    {{ $content ?? '' }}
  </main>

  {{-- trigger alert dan toast berdasarkan session  --}}
  <x-scripts.alert-toast />
  {{-- for javascript --}}
  {{ $scripts ?? '' }}
</body>

</html>
