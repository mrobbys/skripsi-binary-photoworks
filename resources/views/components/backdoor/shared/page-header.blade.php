@props(['title' => null])

<div {{ $attributes->merge(['class' => 'flex justify-between items-center']) }}>
  <h1 class="text-3xl font-bold tracking-tight text-stone-900">{{ $title ?? $slot }}</h1>
  @if (isset($actions))
    <div class="flex items-center gap-2">
      {{ $actions }}
    </div>
  @endif
</div>
