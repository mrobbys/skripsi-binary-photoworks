@props(['team'])

<article class="min-w-65 w-full border border-stone-200 bg-stone-50 sm:w-[calc(33.333%-1rem)]">

  {{-- Foto anggota tim --}}
  <div class="overflow-hidden">
    <img
      src="{{ asset($team['image']) }}"
      alt="Foto {{ $team['name'] }}, {{ $team['role'] }} di Binary Photoworks"
      class="aspect-4/5 w-full object-cover grayscale transition-transform duration-500 hover:scale-105"
      loading="lazy"
    />
  </div>

  {{-- Identitas --}}
  <div class="border-t border-stone-200 px-4 py-4">
    <p class="font-semibold text-stone-900">{{ $team['name'] }}</p>
    <p class="mt-0.5 text-xs font-semibold uppercase tracking-widest text-stone-400">
      {{ $team['role'] }}
    </p>
  </div>

</article>
