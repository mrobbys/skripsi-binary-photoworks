@props([
  'for' => null,
  'required' => false,
])

<label
  @if($for) for="{{ $for }}" @endif
  {{ $attributes->merge(['class' => 'block text-xs font-semibold uppercase tracking-wider text-stone-600']) }}
>
  {{ $slot }}
  @if($required)
    <span class="text-red-600">*</span>
  @endif
</label>
