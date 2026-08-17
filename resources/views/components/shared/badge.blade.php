@props([
    'value' => null,
    'variant' => 'secondary',
    'icon' => null,
    'alpine' => null,
    'size' => 'xs',
])

@php
  $baseClasses = 'inline-flex items-center gap-1 font-bold uppercase tracking-wider select-none whitespace-nowrap';

  $sizeClasses = [
      'xs' => 'px-2 py-0.5 text-[10px]',
      'sm' => 'px-3 py-1 text-xs',
      'md' => 'px-4 py-1.5 text-sm',
  ][$size] ?? 'px-2 py-0.5 text-[10px]';

  $variantClasses = [
      'primary' => 'bg-stone-800 text-stone-50 border border-stone-900',
      'secondary' => 'bg-stone-100 text-stone-600 border border-stone-200',
      'neutral' => 'bg-stone-200 text-stone-700 border border-stone-300',
      'success' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
      'info' => 'bg-blue-50 text-blue-600 border border-blue-200',
      'warning' => 'bg-yellow-50 text-yellow-600 border border-yellow-200',
      'danger' => 'bg-red-50 text-red-600 border border-red-200',
      'lime' => 'bg-green-50 text-lime-600 border border-green-200',
  ][$variant] ?? 'bg-stone-100 text-stone-600 border border-stone-200';
@endphp

@if ($alpine)
  <template x-if="{{ $alpine }}">
    <span {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}>
      @if ($icon)
        <i class="{{ $icon }} text-xs"></i>
      @endif
      {{ $value ?? $slot }}
    </span>
  </template>
@else
  <span {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses"]) }}>
    @if ($icon)
      <i class="{{ $icon }} text-xs"></i>
    @endif
    {{ $value ?? $slot }}
  </span>
@endif
