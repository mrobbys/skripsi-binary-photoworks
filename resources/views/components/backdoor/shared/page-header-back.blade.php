@props([
    'href',
    'title' => null,
    'subtitle' => null,
    'backLabel' => 'Kembali',
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
  <div class="flex items-start gap-4">
    <a
      href="{{ $href }}"
      class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-500"
      aria-label="{{ $backLabel }}"
    >
      <i
        class="ri-arrow-left-line text-base"
        aria-hidden="true"
      ></i>
    </a>
    <div class="flex flex-col gap-1">
      <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-lg font-bold tracking-tight text-stone-900 sm:text-xl">{{ $title ?? $slot }}</h1>
        @if (isset($badge))
          {{ $badge }}
        @endif
      </div>
      @if ($subtitle)
        <p class="text-sm text-stone-500">{{ $subtitle }}</p>
      @elseif (isset($subcontent))
        {{ $subcontent }}
      @endif
    </div>
  </div>

  @if (isset($actions))
    <div class="flex items-center gap-2">
      {{ $actions }}
    </div>
  @endif
</div>
