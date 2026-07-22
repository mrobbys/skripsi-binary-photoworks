@props(['title', 'description' => null, 'action' => '#'])

<div class="report-card flex h-full flex-col border border-stone-200 bg-stone-100/60 p-6">
  {{-- header start --}}
  <div class="mb-5 flex items-start gap-3 border-b border-stone-200 pb-4">
    <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-stone-800 text-stone-50">
      <i
        class="ri-file-text-line text-xl"
        aria-hidden="true"
      ></i>
    </div>
    <div>
      <h2 class="text-base font-semibold text-stone-900">{{ $title }}</h2>
      @if ($description)
        <p class="mt-1 text-xs leading-relaxed text-stone-500">{{ $description }}</p>
      @endif
    </div>
  </div>
  {{-- header end --}}

  {{-- form start --}}
  <form
    action="{{ $action }}"
    class="flex flex-1 flex-col justify-between space-y-4"
    target="_blank"
  >
    <div class="space-y-4">
      {{ $slot }}
    </div>

    <div class="pt-2">
      <x-shared.button
        type="submit"
        value="EKSPOR PDF"
        size="sm"
        class="w-full bg-stone-600 font-semibold uppercase tracking-wider text-stone-50 transition-colors duration-300 hover:bg-stone-700 active:bg-stone-800 focus:scale-105"
      >
        <x-slot:iconLeft>
          <i
            class="ri-download-line text-base"
            aria-hidden="true"
          ></i>
        </x-slot:iconLeft>
      </x-shared.button>
    </div>
  </form>
  {{-- form end --}}
</div>
