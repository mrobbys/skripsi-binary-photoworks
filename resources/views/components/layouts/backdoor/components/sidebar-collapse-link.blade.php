{{-- 
  * COMPONENT SIDEBAR COLLAPSE LINK
  * Item link di dalam dropdown menu sidebar

  * Props : 
      * `href` : string
        Link href
      * `title` : string
        Judul Hlaaman
--}}

@props([
    "href" => "#",
    "title" => "",
])

@php
  $isActive = request()->url() === url($href);
@endphp

<li>
  <a
    href="{{ $href }}"
    class="flex items-center pl-8 pr-2 py-2 text-sm font-medium transition-colors uppercase {{ $isActive ? "text-stone-50 bg-stone-600" : "text-stone-400 hover:bg-stone-500 hover:text-stone-50" }}"
  >
    {{ $title }}
  </a>
</li>
