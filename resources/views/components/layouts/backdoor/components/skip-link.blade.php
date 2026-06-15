{{--
   * COMPONENT BACKDOOR SKIP LINK
   * Tautan lompati ke konten utama untuk aksesibilitas (a11y)
--}}

@props([
    'target' => '#main-content',
    'label' => 'Lompati ke konten utama'
])

<a
  href="{{ $target }}"
  class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-sm focus:bg-stone-850 focus:px-4 focus:py-2 focus:text-stone-50 focus:outline-hidden"
>
  {{ $label }}
</a>
