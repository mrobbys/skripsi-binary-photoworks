{{-- Digunakan di dalam x-for="slot in state.availableSlots" --}}
<x-shared.button
  type="button"
  variant="custom"
  x-on:click="selectSlot(slot)"
  x-bind:class="state.selectedSlot?.start_time === slot.start_time ?
      'border-stone-800 bg-stone-100 font-semibold text-stone-900' :
      'border-stone-200 bg-stone-50 text-stone-600 hover:border-stone-300'"
  class="w-full border py-3 text-sm"
>
  <span x-text="slot.start_time"></span>
</x-shared.button>
