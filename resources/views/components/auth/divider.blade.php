@props([
    'text' => 'ATAU',
])

<div class="relative my-6 text-center">
  <div class="absolute inset-0 flex items-center">
    <div class="w-full border-t border-stone-200"></div>
  </div>
  @if ($text)
    <span class="relative bg-stone-50 px-4 text-xs font-semibold tracking-wider text-stone-400">
      {{ $text }}
    </span>
  @endif
</div>
