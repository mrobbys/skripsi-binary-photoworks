@props([
    'active' => false,
    'href' => '#',
    'title' => '',
])

<li class="py-4">
  <a href="{{ $href }}"
    class="transition-colors {{ $active ? 'font-bold text-stone-900 border-b-2 border-stone-900 pb-1 inline-block' : 'font-medium text-stone-500 hover:text-stone-900 block' }}"
    aria-current="{{ $active ? 'page' : 'false' }}">
    {{ $title }}
  </a>
</li>
