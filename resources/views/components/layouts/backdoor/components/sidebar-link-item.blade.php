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
    'href' => '#',
    'icon' => 'ri-dashboard-line',
    'title' => 'Dashboard',
    'active' => null,
])

@php
  $isActive = $active ?? (request()->url() === url($href));
@endphp

<li>
  <a
    href="{{ $href }}"
    @if($isActive) aria-current="page" @endif
    class="flex items-center gap-4 px-4 py-2 text-sm font-medium border-l-2 transition-all focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-stone-300 {{ $isActive ? 'bg-stone-600 border-stone-50 text-stone-50' : 'border-transparent hover:bg-stone-600/50 text-stone-300 hover:text-stone-50' }}">
    <i class="{{ $icon }} text-xl" aria-hidden="true"></i>
    <span>{{ $title }}</span>
  </a>
</li>
