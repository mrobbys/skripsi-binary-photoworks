{{-- Digunakan di dalam x-for="slot in state.availableSlots" --}}
<button type="button" class="border py-3 text-sm font-medium transition-colors text-center"
  x-bind:class="{
      'border-stone-500 bg-stone-100 text-stone-900': state.selectedSlot?.start_time === slot.start_time && !slot
          .is_occupied,
      'border-stone-200 bg-stone-100 text-stone-400 cursor-not-allowed line-through': slot.is_occupied,
      'border-stone-200 bg-white text-stone-600 hover:border-stone-300': !slot.is_occupied && state.selectedSlot
          ?.start_time !== slot.start_time,
  }"
  x-bind:disabled="slot.is_occupied"
  x-on:click="selectSlot(slot)"
  x-text="slot.start_time">
</button>
