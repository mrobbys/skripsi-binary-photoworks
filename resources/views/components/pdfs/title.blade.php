@props([
    'value' => null,
])

<h1 class="mb-6 text-center text-lg font-extrabold uppercase tracking-tight text-stone-900">
  {{ $value ?? $slot }}
</h1>
