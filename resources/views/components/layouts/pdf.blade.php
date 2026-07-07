@props([
    'title' => 'Kuitansi Pembayaran',
])

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
  <link
    rel="icon"
    href="{{ public_path('assets/binary-logo/binary-logo-white.png') }}"
    type="image/png" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif !important;
    }
  </style>
</head>

<body {{ $attributes->merge(['class' => 'bg-stone-50 antialiased text-stone-900']) }}>
  {{ $slot }}
</body>

</html>
