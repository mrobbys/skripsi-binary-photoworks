@props([
    'value' => null,
    'variant' => 'secondary',
    'icon' => null,
    'alpine' => null,
])

@php
  $baseClasses = 'inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider select-none';

  $variantClasses = [
      'primary' => 'bg-stone-800 text-stone-50 border border-stone-900',
      'secondary' => 'bg-stone-100 text-stone-600 border border-stone-200',
      'neutral' => 'bg-stone-200 text-stone-700 border border-stone-300',
      'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
  ][$variant] ?? 'bg-stone-100 text-stone-600 border border-stone-200';
@endphp

@if ($alpine)
  <template x-if="{{ $alpine }}">
    <span {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
      @if ($icon)
        <i class="{{ $icon }} text-xs"></i>
      @endif
      {{ $value ?? $slot }}
    </span>
  </template>
@else
  <span {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    @if ($icon)
      <i class="{{ $icon }} text-xs"></i>
    @endif
    {{ $value ?? $slot }}
  </span>
@endif
