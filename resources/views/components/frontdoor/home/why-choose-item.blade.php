@props([
    'title' => '',
    'description' => '',
])

<div data-animate="why-choose-item" class="flex items-start gap-4 sm:gap-5">
  {{-- Square Icon Container --}}
  <div class="mt-1 h-6 w-6 shrink-0 bg-stone-500"></div>

  {{-- Content --}}
  <div class="space-y-1">
    <h3 class="text-base font-semibold text-stone-900 sm:text-lg">
      {{ $title }}
    </h3>
    <p class="text-sm leading-relaxed text-stone-600 sm:text-base">
      {{ $description }}
    </p>
  </div>
</div>
