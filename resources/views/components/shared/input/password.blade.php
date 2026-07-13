@props([
  'id',
  'model' => null,
  'placeholder' => '••••••••',
  'required' => false,
])

<div x-data="{ show: false }" class="relative w-full">
  <input
    x-bind:type="show ? 'text' : 'password'"
    id="{{ $id }}"
    @if($model) x-model="{{ $model }}" @endif
    autocomplete="off"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => 'w-full border text-sm border-stone-200 px-4 py-2.5 pr-10 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors']) }}>

  <button
    type="button"
    tabindex="-1"
    x-on:click="show = !show"
    class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-500 hover:text-stone-700 focus:outline-none cursor-pointer transition-colors duration-300"
    aria-label="Tampilkan/Sembunyikan Password">
    <i x-bind:class="show ? 'ri-eye-off-line' : 'ri-eye-line'" class="text-[1.15rem]"></i>
  </button>
</div>
