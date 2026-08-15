@props(['href', 'title' => null, 'subtitle' => null, 'backLabel' => 'Kembali'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
  <div class="flex items-center gap-4">
    <a
      href="{{ $href }}"
      class="flex h-8 w-8 shrink-0 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-500"
      aria-label="{{ $backLabel }}"
    >
      <i
        class="ri-arrow-left-line text-base"
        aria-hidden="true"
      ></i>
    </a>
    <div>
      <h1 class="text-lg font-bold tracking-tight text-stone-900 sm:text-xl">{{ $title ?? $slot }}</h1>
      @if ($subtitle)
        <p class="text-sm text-stone-500">{{ $subtitle }}</p>
      @endif
    </div>
  </div>

  @if (isset($actions))
    <div class="flex items-center gap-2">
      {{ $actions }}
    </div>
  @endif
</div>
