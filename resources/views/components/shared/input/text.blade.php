@props(['name', 'type' => 'text', 'placeholder' => '', 'required' => false])

<input
  type="{{ $type }}"
  name="{{ $name }}"
  id="{{ $name }}"
  autocomplete="off"
  placeholder="{{ $placeholder }}"
  {{ $required ? 'required' : '' }}
  x-bind:class="(state.errors.{{ $name }} || (@js($errors->has($name)) && !state.dismissedErrors.{{ $name }})) ?
  'border-red-600 focus:border-red-600' : 'border-stone-300 focus:border-stone-900'"
  {{ $attributes->merge(['class' => 'w-full border-b p-2 text-sm text-stone-900 transition-colors placeholder:text-stone-400 focus:outline-none bg-transparent ring-0']) }}
>
