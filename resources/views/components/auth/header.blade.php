@props([
  'title',
  'subtitle' => null,
  'center' => false,
])

<div class="mb-8 {{ $center ? 'text-center' : '' }}">
  <h1 class="font-serif text-4xl font-normal tracking-tight text-stone-900">
    {{ $title }}
  </h1>
  @if($subtitle)
    <p class="mt-2 text-sm text-stone-500">
      {{ $subtitle }}
    </p>
  @endif
</div>
