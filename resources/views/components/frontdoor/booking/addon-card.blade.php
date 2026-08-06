<div
  role="button"
  tabindex="0"
  class="flex cursor-pointer select-none flex-col justify-between gap-3.5 border p-4 transition-colors sm:flex-row sm:items-center sm:gap-4 sm:p-6"
  x-on:click="toggleAddon(addon.id, addon.has_quantity)"
  x-on:keydown.enter.prevent="$el.click()"
  x-on:keydown.space.prevent="$el.click()"
  x-bind:class="isAddonSelected(addon.id) ? 'border-stone-900 bg-stone-50' : 'border-stone-200 bg-white hover:border-stone-300'"
>

  <div class="flex min-w-0 flex-1 items-start gap-3.5 sm:items-center sm:gap-4">
    {{-- checkbox start --}}
    <div
      class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center border transition-colors sm:mt-0"
      x-bind:class="isAddonSelected(addon.id) ? 'border-stone-950 bg-stone-900' : 'border-stone-300 bg-white'"
    >
      <i
        class="ri-check-line text-sm text-white"
        x-show="isAddonSelected(addon.id)"
        aria-hidden="true"
      ></i>
    </div>
    {{-- checkbox end --}}

    <div class="min-w-0 flex-1">
      <span
        class="block text-base font-bold leading-snug text-stone-900"
        x-text="addon.name"
      ></span>
      <span
        class="mt-0.5 block text-sm font-semibold text-stone-700"
        x-text="formatRupiah(addon.price)"
      ></span>
      <template x-if="addon.description">
        <span
          class="mt-1 block text-xs leading-relaxed text-stone-500"
          x-text="addon.description"
        ></span>
      </template>
    </div>
  </div>

  {{-- checkbox counter (if has_quantity = true) start --}}
  <template x-if="addon.has_quantity">
    <div
      class="flex w-full items-center justify-between border-t border-stone-200 pt-3 sm:w-auto sm:justify-end sm:border-t-0 sm:pt-0"
      x-on:click.stop
    >
      <span class="text-xs font-medium text-stone-500 sm:hidden">Jumlah:</span>
      <div class="flex shrink-0 items-center border border-stone-300 bg-white">
        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center border-r border-stone-300 bg-stone-100 text-lg text-stone-600 transition-colors hover:bg-stone-200"
          x-on:click="decrement(addon.id)"
        >
          <i
            class="ri-subtract-line"
            aria-hidden="true"
          ></i>
        </button>

        <input
          type="number"
          min="1"
          class="w-12 border-0 p-0 text-center text-sm font-semibold text-stone-900 [appearance:textfield] focus:outline-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
          x-bind:value="getQty(addon.id)"
          x-on:input="setQty(addon.id, $event.target.value)"
          x-on:blur="$el.value = getQty(addon.id)"
        />

        <button
          type="button"
          class="flex h-8 w-8 items-center justify-center border-l border-stone-300 bg-stone-100 text-lg text-stone-600 transition-colors hover:bg-stone-200"
          x-on:click="increment(addon.id)"
        >
          <i
            class="ri-add-line"
            aria-hidden="true"
          ></i>
        </button>
      </div>
    </div>
  </template>
  {{-- checkbox counter (if has_quantity = true) end --}}
</div>
