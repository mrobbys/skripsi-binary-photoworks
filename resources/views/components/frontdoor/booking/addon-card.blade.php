{{-- Digunakan di dalam x-for="addon in state.allAddons" --}}
<div class="border p-4 transition-colors"
  x-bind:class="isAddonSelected(addon.id) ? 'border-stone-500 bg-stone-100' : 'border-stone-200 bg-white'">
  <div class="flex items-start justify-between gap-3">
    <div class="flex-1 min-w-0">
      <span class="font-semibold text-stone-900 block" x-text="addon.name"></span>
      <span class="text-xs text-stone-600"
        x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(addon.price)"></span>
    </div>

    <template x-if="addon.has_quantity && isAddonSelected(addon.id)">
      <div class="flex items-center border border-stone-200 shrink-0">
        <button type="button" class="px-2 py-1 text-stone-500 hover:bg-stone-100 text-lg leading-none"
          x-on:click="decrement(addon.id)">−</button>
        <span class="px-3 text-sm font-medium" x-text="getQty(addon.id)"></span>
        <button type="button" class="px-2 py-1 text-stone-500 hover:bg-stone-100 text-lg leading-none"
          x-on:click="increment(addon.id)">+</button>
      </div>
    </template>

    <template x-if="!addon.has_quantity || !isAddonSelected(addon.id)">
      <button type="button"
        class="shrink-0 w-6 h-6 border flex items-center justify-center transition-colors"
        x-bind:class="isAddonSelected(addon.id) ? 'border-stone-500 bg-stone-500' : 'border-stone-300 bg-white'"
        x-on:click="toggleAddon(addon.id, addon.has_quantity)">
        <i class="ri-check-line text-white text-sm" x-show="isAddonSelected(addon.id)"></i>
      </button>
    </template>
  </div>

  <template x-if="!isAddonSelected(addon.id)">
    <button type="button" class="w-full text-left mt-3 text-xs text-stone-500 font-medium"
      x-on:click="toggleAddon(addon.id, addon.has_quantity)">
      + Tambahkan
    </button>
  </template>
</div>
