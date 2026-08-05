@props(['review'])

<div class="flex flex-col justify-between border border-stone-200 bg-stone-50/60 p-6 text-left sm:p-8">
  <div class="space-y-4">
    {{-- star rating start --}}
    <div
      class="flex items-center gap-1 text-stone-700"
      aria-label="Rating {{ $review->rating ?? 5 }} dari 5 bintang"
    >
      @for ($i = 1; $i <= 5; $i++)
        <i class="{{ $i <= ($review->rating ?? 5) ? 'ri-star-fill' : 'ri-star-line text-stone-300' }} text-base" aria-hidden="true"></i>
      @endfor
    </div>
    {{-- star rating end --}}

    {{-- komentar start --}}
    <p class="text-sm italic leading-relaxed text-stone-500">
      "{{ $review->comment }}"
    </p>
    {{-- komentar end --}}
  </div>

  {{-- nama klien start --}}
  <div class="mt-8">
    <span class="text-xs font-bold uppercase tracking-wider text-stone-900">
      {{ $review->user->name ?? 'Pelanggan Binary' }}
    </span>
  </div>
  {{-- nama klien end --}}
</div>
