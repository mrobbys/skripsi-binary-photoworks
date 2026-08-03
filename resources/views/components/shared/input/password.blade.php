@props([
    'name' => 'password',
    'placeholder' => 'Masukkan password Anda',
    'required' => false,
])

<div
  x-data="{ show: false }"
  class="relative w-full"
>
  <input
    x-bind:type="show ? 'text' : 'password'"
    name="{{ $name }}"
    id="{{ $name }}"
    autocomplete="off"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    x-bind:class="(state.errors.{{ $name }} || ({{ $name === 'password' ? 'true' : 'false' }} && state.passwordErrors && state.passwordErrors.length > 0) || (@js($errors->has($name)) && !state.dismissedErrors.{{ $name }})) ?
    'border-red-600 focus:border-red-600' : 'border-stone-300 focus:border-stone-900'"
    {{ $attributes->merge(['class' => 'w-full border p-2 text-sm text-stone-900 transition-colors placeholder:text-stone-400 focus:outline-none bg-transparent pr-10 ring-0']) }}
  >

  <button
    type="button"
    tabindex="-1"
    x-on:click="show = !show"
    class="absolute right-1 top-1/2 -translate-y-1/2 cursor-pointer p-1 text-stone-500 hover:text-stone-800 focus:outline-none"
    aria-label="Tampilkan/Sembunyikan Password"
  >
    <i
      x-bind:class="show ? 'ri-eye-off-line' : 'ri-eye-line'"
      class="text-lg"
    ></i>
  </button>
</div>
