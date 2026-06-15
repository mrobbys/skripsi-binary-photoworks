{{-- 
  * Sidebar Link Item

  * Props : 
      * `href` : string
        Link href
      * `icon` : string
        Icon dari remix icon
      * `title` : string
        Judul Hlaaman
  
--}}

@props([
    "href" => "#",
    "icon" => "ri-dashboard-line",
    "title" => "Dashboard",
])

@php
  $isActive = request()->url() === url($href);
@endphp

<li>
  <a
    href="{{ $href }}"
    class="flex items-center gap-2 px-2 py-1.5 text-sm font-medium underline-offset-2 border-l-2 transition-all {{ $isActive ? "bg-stone-600 border-stone-50 text-stone-50" : "border-transparent hover:bg-stone-500 text-stone-400 hover:text-stone-50" }}"
  >
    <i class="{{ $icon }} text-3xl"></i>
    <span class="uppercase font-medium">{{ $title }}</span>
  </a>
</li>
