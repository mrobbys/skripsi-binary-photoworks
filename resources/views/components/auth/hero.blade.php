@props([
  'image' => asset('assets/images/auth-hero.png'),
  'alt' => 'Binary Photoworks Hero',
])

<div class="hidden w-1/2 lg:block">
  <div class=" w-full overflow-hidden">
    <img
      src="{{ $image }}"
      alt="{{ $alt }}"
      class="h-full w-full object-cover object-bottom"
    >
  </div>
</div>
