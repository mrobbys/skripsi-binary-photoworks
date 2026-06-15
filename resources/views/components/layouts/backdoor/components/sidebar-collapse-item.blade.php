{{-- 
  * COMPONENT SIDEBAR COLLAPSE ITEM
  * Untuk navigasi menu dropdown di sidebar

  * Props : 
      * `title` : string
        Judul menu dropdown
      * `icon` : string
        Icon dari remix icon
      * `active` : boolean
        Untuk menandai menu aktif

  * Slot :
      - Berisikan @component('layouts.backdoor.components.sidebar-collapse-link')
--}}

@props([
    'title' => '',
    'icon' => 'ri-folder-line',
    'active' => false,
])

<div
  x-data="{ isExpanded: {{ $active ? "true" : "false" }} }"
  class="flex flex-col"
>
  <button
    type="button"
    x-on:click="isExpanded = ! isExpanded"
    x-bind:aria-expanded="isExpanded ? 'true' : 'false'"
    class="w-full flex items-center cursor-pointer justify-between gap-2 px-2 py-1.5 text-sm font-medium  underline-offset-2 transition-colors text-left border-l-2 {{ $active ? "bg-stone-600 border-stone-50 text-stone-50" : "border-transparent hover:bg-stone-500 text-stone-400 hover:text-stone-50" }}"
  >
    <div class="flex items-center gap-2">
      <i class="{{ $icon }} text-3xl"></i>
      <span class="uppercase font-medium">{{ $title }}</span>
    </div>

    <i
      class="ri-arrow-down-s-line text-xl transition-transform shrink-0"
      x-bind:class="isExpanded ? 'rotate-180' : 'rotate-0'"
    ></i>
  </button>

  <ul
    x-cloak
    x-collapse
    x-show="isExpanded"
    class="flex flex-col gap-2 my-2"
  >
    {{ $slot }}
  </ul>
</div>
