<div class="border p-6 transition-colors flex items-center justify-between gap-4 cursor-pointer select-none"
  x-on:click="toggleAddon(addon.id, addon.has_quantity)"
  x-bind:class="isAddonSelected(addon.id) ? 'border-stone-900 bg-stone-50' : 'border-stone-200 bg-white hover:border-stone-300'">

  <div class="flex items-center gap-4 flex-1 min-w-0">
    {{-- checkbox start --}}
    <div class="shrink-0 w-6 h-6 border flex items-center justify-center transition-colors"
      x-bind:class="isAddonSelected(addon.id) ? 'border-stone-950 bg-stone-900' : 'border-stone-300 bg-white'">
      <i class="ri-check-line text-white text-sm" x-show="isAddonSelected(addon.id)"></i>
    </div>
    {{-- checkbox end --}}

    <div class="min-w-0">
      <span class="font-bold text-stone-900 block text-base" x-text="addon.name"></span>
      <span class="text-sm font-semibold text-stone-700 mt-0.5 block" x-text="formatRupiah(addon.price)"></span>
      <template x-if="addon.description">
        <span class="text-xs text-stone-500 mt-1 block" x-text="addon.description"></span>
      </template>
    </div>
  </div>

  {{-- checkbox counter (if has_quantity = true) start --}}
  <template x-if="addon.has_quantity">
    <div class="flex items-center border border-stone-300 shrink-0 bg-white" x-on:click.stop>
      <button type="button"
        class="w-8 h-8 flex items-center justify-center text-stone-600 bg-stone-100 hover:bg-stone-200 text-lg transition-colors border-r border-stone-300"
        x-on:click="decrement(addon.id)">
        <i class="ri-subtract-line"></i>
      </button>

      <input
        type="number"
        min="1"
        class="w-12 text-center text-sm font-semibold text-stone-900 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none rounded-none border-0 p-0"
        x-bind:value="getQty(addon.id)"
        x-on:input="setQty(addon.id, $event.target.value)"
        x-on:blur="$el.value = getQty(addon.id)" />

      <button type="button"
        class="w-8 h-8 flex items-center justify-center text-stone-600 bg-stone-100 hover:bg-stone-200 text-lg transition-colors border-l border-stone-300"
        x-on:click="increment(addon.id)">
        <i class="ri-add-line"></i>
      </button>
    </div>
  </template>
  {{-- checkbox counter (if has_quantity = true) end --}}
</div>
