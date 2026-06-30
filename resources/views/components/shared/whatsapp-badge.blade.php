@props([
    'alpine' => null,
])

@if ($alpine)
  <template x-if="{{ $alpine }}">
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider select-none']) }}>
      <i class="ri-whatsapp-line text-xs"></i>
      WA Only
    </span>
  </template>
@else
  <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 bg-stone-200 text-stone-700 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider select-none']) }}>
    <i class="ri-whatsapp-line text-xs"></i>
    WA Only
  </span>
@endif
