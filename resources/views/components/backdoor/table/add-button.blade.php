@props(['text' => 'Tambah'])

<button
  {{ $attributes->merge([
      'type' => 'button',
      'class' =>
          'inline-flex items-center justify-center gap-2 bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-stone-500 focus-visible:ring-offset-2 active:scale-[0.98] transition-all duration-150 font-semibold text-sm tracking-wide cursor-pointer',
  ]) }}>
  <i class="ri-add-line leading-none"></i>
  <span>{{ $text }}</span>
</button>
