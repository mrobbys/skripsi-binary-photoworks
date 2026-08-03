@props([
  'name',
])

<small
  x-cloak
  x-show="state.errors.{{ $name }} || (@js($errors->has($name)) && !state.dismissedErrors.{{ $name }})"
  x-text="state.errors.{{ $name }} || @js($errors->first($name))"
  {{ $attributes->merge(['class' => 'block text-xs font-medium text-red-600']) }}
></small>
