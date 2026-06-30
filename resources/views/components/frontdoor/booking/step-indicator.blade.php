@php
  $steps = [1 => 'Pilih Paket', 2 => 'Jadwal', 3 => 'Add-ons', 4 => 'Ringkasan'];
@endphp
<div class="flex items-center justify-center" x-cloak>
  @foreach ($steps as $num => $label)
    <div class="flex items-center">
      <div class="flex items-center gap-2"
        x-bind:class="{{ $num }} <= state.currentStep ? 'text-stone-900' : 'text-stone-400'">
        <div class="w-7 h-7 border flex items-center justify-center text-sm font-bold shrink-0"
          x-bind:class="{{ $num }} === state.currentStep ?
              'border-stone-900 bg-stone-900 text-white' :
              ({{ $num }} < state.currentStep ?
                  'border-stone-500 bg-stone-100 text-stone-500' :
                  'border-stone-200 bg-white text-stone-400')">
          <template x-if="{{ $num }} < state.currentStep"><i class="ri-check-line text-xs"></i></template>
          <template x-if="{{ $num }} >= state.currentStep"><span>{{ $num }}</span></template>
        </div>
        <span class="text-sm font-medium hidden sm:block">{{ $label }}</span>
      </div>
      @if ($num < count($steps))
        <div class="w-8 sm:w-12 h-px mx-2"
          x-bind:class="{{ $num }} < state.currentStep ? 'bg-stone-500' : 'bg-stone-200'"></div>
      @endif
    </div>
  @endforeach
</div>
