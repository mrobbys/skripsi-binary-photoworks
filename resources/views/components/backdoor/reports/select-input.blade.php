@props([
    'name',
    'label' => 'STATUS',
    'placeholder' => 'Semua Status',
    'options' => [],
    'value' => null,
])

<div>
  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-700">
    {{ $label }}
  </label>
  <select
    name="{{ $name }}"
    x-data="choices({ searchEnabled: false, shouldSort: false, placeholder: true, placeholderValue: '{{ $placeholder }}' })"
    {{ $attributes->merge(['class' => 'w-full border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm text-stone-900 transition focus:border-stone-700 focus:bg-white focus:outline-none focus:ring-0']) }}
  >
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $optValue => $optLabel)
      <option value="{{ $optValue }}" @selected($value == $optValue)>{{ $optLabel }}</option>
    @endforeach
  </select>
</div>
