{{-- addon selection card start --}}
<div class="border border-stone-300">
  <div class="flex items-center justify-between border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">
      Layanan Tambahan <span class="text-xs font-normal text-stone-600">(Opsional)</span>
    </h2>
    <button
      type="button"
      x-on:click="addAddonRow()"
      class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-stone-600 transition-colors hover:text-stone-900"
    >
      <i class="ri-add-line" aria-hidden="true"></i> Tambah Layanan
    </button>
  </div>
  <div class="space-y-3 p-5">

    <template x-if="state.addons.length === 0">
      <div class="space-y-1 text-xs italic text-stone-400">
        <p>Belum ada layanan tambahan</p>
        <p>Klik "+ Tambah Layanan" untuk menambahkan</p>
      </div>
    </template>

    <template
      x-for="(item, index) in state.addons"
      :key="item.id"
    >
      <div class="flex items-center gap-3">
        <div class="flex-1">
          <select
            x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
            x-init="initAddonChoices($el, $watch, item)"
            x-modelable="value"
            x-model="item.addon_id"
            x-on:change="onAddonChange(item)"
          >
            <option value=""></option>
          </select>
        </div>

        {{-- input qty start --}}
        <div class="w-20">
          <input
            type="number"
            aria-label="Jumlah Layanan"
            x-bind:value="isQtyDisabled(item) ? 1 : item.quantity"
            x-on:input="item.quantity = isQtyDisabled(item) ? 1 : Math.max(1, Number($event.target.value))"
            x-bind:disabled="isQtyDisabled(item)"
            min="1"
            class="w-full border border-stone-300 bg-stone-50 px-3 py-2 text-center text-sm text-stone-900 focus:border-stone-500 focus:outline-none disabled:bg-stone-100 disabled:text-stone-400"
          >
        </div>
        {{-- input qty end --}}

        {{-- hapus addon start --}}
        <button
          type="button"
          x-on:click="removeAddonRow(index)"
          aria-label="Hapus Layanan"
          class="p-1 text-stone-400 transition-colors hover:text-red-600"
        >
          <i class="ri-delete-bin-line text-lg leading-none" aria-hidden="true"></i>
        </button>
        {{-- hapus addon end --}}

      </div>
    </template>

  </div>
</div>
{{-- addon selection card end --}}
