@props([
  'name',
  'rows' => 4,
  'placeholder' => '',
  'required' => false,
])

<textarea
  name="{{ $name }}"
  id="{{ $name }}"
  rows="{{ $rows }}"
  placeholder="{{ $placeholder }}"
  {{ $required ? 'required' : '' }}
  x-bind:class="(state.errors.{{ $name }} || (@js($errors->has($name)) && !state.dismissedErrors.{{ $name }})) ? 'border-red-600 focus:border-red-600' : 'border-stone-300 focus:border-stone-900'"
  {{ $attributes->merge(['class' => 'w-full border-b p-2 text-sm text-stone-900 transition-colors placeholder:text-stone-400 focus:outline-none bg-transparent resize-y ring-0']) }}
>{{ $slot }}</textarea>
