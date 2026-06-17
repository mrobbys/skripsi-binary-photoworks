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
    'href' => '#',
    'title' => '',
])

@php
  $isActive = request()->url() === url($href);
@endphp

<li>
  <a
    href="{{ $href }}"
    class="flex items-center px-8 py-2 text-sm font-medium transition-colors {{ $isActive ? 'text-stone-50 bg-stone-600' : 'text-stone-300 hover:bg-stone-600/30 hover:text-stone-50' }}">
    {{ $title }}
  </a>
</li>
