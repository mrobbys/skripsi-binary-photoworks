@props([
  'image' => asset('assets/images/auth-hero.png'),
  'alt' => 'Binary Photoworks Hero',
])

<div class="hidden w-1/2 lg:block">
  <div class="h-full w-full overflow-hidden">
    <img
      src="{{ $image }}"
      alt="{{ $alt }}"
      class="max-h-175 h-full w-full object-cover object-bottom"
    >
  </div>
</div>
