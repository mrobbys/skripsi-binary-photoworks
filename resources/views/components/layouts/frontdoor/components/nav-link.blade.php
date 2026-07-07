@props([
    'active' => false,
    'href' => '#',
    'title' => '',
])

<li>
  <a href="{{ $href }}"
    class="{{ $active ? 'font-bold text-stone-900 border-b-2 border-stone-900 pb-1' : 'font-medium text-stone-500 hover:text-stone-900' }} transition-colors"
    aria-current="{{ $active ? 'page' : 'false' }}">
    {{ $title}}
  </a>
</li>
