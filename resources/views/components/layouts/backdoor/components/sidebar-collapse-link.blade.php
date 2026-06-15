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
    'title' => ''
])

<li>
  <a
    href="{{ $href }}"
    class="flex items-center pl-8 pr-2 py-2 text-sm font-medium text-stone-300 hover:bg-stone-500 hover:text-stone-50 transition-colors uppercase"
  >
    {{ $title }}
  </a>
</li>
