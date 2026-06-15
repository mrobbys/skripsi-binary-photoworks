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
])

<li>
  <a
    href="{{ $href }}"
    class="flex items-center rounded-sm gap-2 px-2 py-1.5 text-sm font-medium text-stone-50 underline-offset-2 hover:bg-stone-500"
  >
    <i class="{{ $icon }} text-3xl"></i>
    <span class="uppercase font-semibold">{{ $title }}</span>
  </a>
</li>
