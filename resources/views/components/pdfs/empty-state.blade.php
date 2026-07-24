@props([
    'message' => 'Tidak ada data laporan pada periode filter ini.',
])

<div {{ $attributes->merge(['class' => 'py-10 text-center']) }}>
  <p class="text-base font-semibold text-stone-500">{{ $message }}</p>
</div>
