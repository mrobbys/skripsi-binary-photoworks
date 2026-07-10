{{-- Digunakan di dalam x-for="slot in state.availableSlots" --}}
<x-shared.button
  type="button"
  size="custom"
  class="w-full border py-3 text-sm"
  x-bind:class="{
      'border-stone-500 bg-stone-100 text-stone-900': state.selectedSlot?.start_time === slot.start_time,
      'border-stone-200 bg-white text-stone-600 hover:border-stone-300': state.selectedSlot?.start_time !== slot.start_time,
  }"
  x-on:click="selectSlot(slot)">
  <span x-text="slot.start_time"></span>
</x-shared.button>
