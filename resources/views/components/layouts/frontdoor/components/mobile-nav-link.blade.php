@props([
    'active' => false,
    'href' => '#',
    'title' => '',
])

<li class="py-4">
  <a href="{{ $href }}"
    class="w-full text-lg {{ $active ? 'font-bold text-stone-900' : 'font-medium text-stone-500' }}"
    aria-current="{{ $active ? 'page' : 'false' }}">
    {{ $title }}
  </a>
</li>
