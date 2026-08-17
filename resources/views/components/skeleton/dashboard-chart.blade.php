@props([
    'type' => 'bar', // 'bar', 'line', 'pie'
])

{{-- chart skeleton start --}}
<div class="flex h-64 w-full animate-pulse flex-col justify-between py-2 sm:h-72 sm:py-4">
  @if ($type === 'pie')
    {{-- skeleton pie/doughnut start --}}
    <div class="flex flex-1 items-center justify-center">
      <div class="h-32 w-32 border-[6px] border-stone-200 bg-transparent sm:h-40 sm:w-40 sm:border-8"></div>
    </div>
    <div class="mt-3 flex flex-wrap justify-center gap-2 sm:mt-4 sm:gap-4">
      <div class="h-2.5 w-12 bg-stone-200 sm:h-3 sm:w-16"></div>
      <div class="h-2.5 w-16 bg-stone-200 sm:h-3 sm:w-20"></div>
      <div class="h-2.5 w-10 bg-stone-200 sm:h-3 sm:w-14"></div>
    </div>
    {{-- skeleton pie/doughnut end --}}
  @else
    {{-- skeleton bar/line start --}}
    <div class="flex flex-1 items-end justify-between gap-1.5 border-b border-stone-200 pb-2 sm:gap-2 md:gap-3">
      <div class="h-1/3 w-full bg-stone-200"></div>
      <div class="h-2/3 w-full bg-stone-200"></div>
      <div class="h-1/2 w-full bg-stone-200"></div>
      <div class="h-4/5 w-full bg-stone-200"></div>
      <div class="h-3/5 w-full bg-stone-200"></div>
      <div class="h-full w-full bg-stone-200"></div>
      <div class="h-2/5 w-full bg-stone-200"></div>
      <div class="h-3/4 w-full bg-stone-200"></div>
      <div class="h-1/2 w-full bg-stone-200"></div>
      <div class="h-4/5 w-full bg-stone-200"></div>
      <div class="h-3/5 w-full bg-stone-200"></div>
      <div class="h-2/3 w-full bg-stone-200"></div>
    </div>
    <div class="mt-2 flex justify-between gap-1 sm:gap-2">
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
      <div class="h-2 w-4 bg-stone-200 sm:h-2.5 sm:w-6"></div>
    </div>
    {{-- skeleton bar/line end --}}
  @endif
</div>
{{-- chart skeleton end --}}
