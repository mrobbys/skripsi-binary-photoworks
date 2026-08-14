@props(['placeholder' => ''])

<div {{ $attributes->merge(['class' => 'w-full sm:w-80 duration-300 relative']) }}>
  <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">
    <i
      class="ri-search-line"
      aria-hidden="true"
    ></i>
  </span>
  <input
    type="text"
    x-bind:value="table.search"
    x-on:input="table.setSearch($event.target.value)"
    placeholder="{{ $placeholder }}"
    class="w-full border border-stone-300 bg-stone-50 py-2 pl-9 pr-9 text-sm text-stone-900 placeholder-stone-400 transition-colors duration-300 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500"
  >
  <button
    x-show="table.search"
    x-cloak
    x-on:click="table.setSearch('')"
    type="button"
    class="absolute inset-y-0 right-0 flex items-center pr-3 text-stone-400 transition-colors duration-300 hover:text-stone-600"
    aria-label="Hapus pencarian"
  >
    <i
      class="ri-close-line text-lg"
      aria-hidden="true"
    ></i>
  </button>
</div>
