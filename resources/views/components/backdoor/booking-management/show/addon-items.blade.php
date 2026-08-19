{{-- addon items card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Layanan Tambahan</h2>
  </div>

  <template x-if="!state.booking?.addons?.length">
    <p class="px-5 py-4 text-xs italic text-stone-400">Tidak ada layanan tambahan</p>
  </template>

  <template x-if="state.booking?.addons?.length">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="border-b border-stone-300 bg-stone-50">
          <tr>
            <th class="px-5 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Layanan</th>
            <th class="px-5 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-stone-600">Qty</th>
            <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-600">Harga Satuan</th>
            <th class="px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-600">Subtotal</th>
            <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
              <th class="w-16 px-5 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-600">Aksi</th>
            </template>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-300">
          <template
            x-for="addon in state.booking?.addons"
            :key="addon.id"
          >
            <tr>
              <td
                class="px-5 py-3 font-medium text-stone-800 whitespace-nowrap"
                x-text="addon.name"
              ></td>
              <td
                class="px-5 py-3 text-center text-stone-600"
                x-text="addon.pivot.quantity"
              ></td>
              <td
                class="px-5 py-3 text-right text-stone-600 whitespace-nowrap"
                x-text="formatRupiah(addon.pivot.price_at_purchase)"
              ></td>
              <td
                class="px-5 py-3 text-right font-semibold text-stone-800 whitespace-nowrap"
                x-text="formatRupiah(addon.pivot.quantity * addon.pivot.price_at_purchase)"
              ></td>
              <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
                <td class="px-5 py-3 text-right">
                  <button
                    type="button"
                    x-on:click="removeAddon(addon.id)"
                    x-bind:disabled="state.upsell.isLoading"
                    class="text-red-500 transition hover:text-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                    title="Hapus Layanan"
                    aria-label="Hapus Layanan"
                  >
                    <i class="ri-delete-bin-line leading-none" aria-hidden="true"></i>
                  </button>
                </td>
              </template>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </template>

  {{-- inline form upsell start --}}
  <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
    <div class="border-t border-stone-300 bg-stone-100 px-5 py-4">
      <div class="mb-3">
        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">Tambahkan Add-on Baru</h3>
        <p class="text-xs text-stone-500">Pilih item add-on yang ingin dimasukkan ke dalam pesanan ini.</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
          <select
            x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
            x-init="const updateChoices = () => {
                if (!$el._choices) return;
                const currentAddons = state.booking?.addons || [];
                const mapped = state.allAddons
                    .filter(a => {
                        if (!a.has_quantity) {
                            return !currentAddons.some(booked => String(booked.id) === String(a.id));
                        }
                        return true;
                    })
                    .map(a => ({
                        value: String(a.id),
                        label: a.name + ' — ' + formatRupiah(a.price)
                    }));
                $el._choices.clearStore();
                $el._choices.setChoices([{ value: '', label: '--- Pilih Layanan ---', placeholder: true }, ...mapped], 'value', 'label', true);
            };
            setTimeout(() => updateChoices(), 50);
            $watch('state.allAddons', updateChoices);
            $watch('state.booking?.addons', updateChoices, { deep: true });
            $watch('state.upsell.addonId', () => onUpsellAddonChange());"
            x-modelable="value"
            x-model="state.upsell.addonId"
            x-on:change="onUpsellAddonChange()"
          >
          </select>
        </div>
        <div class="w-full sm:w-20">
          <input
            type="number"
            aria-label="Jumlah Add-on"
            x-bind:value="(state.upsell.addonId && !state.allAddons.find(a => a.id == state.upsell.addonId)?.has_quantity) ? 1 : state.upsell.quantity"
            x-on:input="state.upsell.quantity = (state.upsell.addonId && !state.allAddons.find(a => a.id == state.upsell.addonId)?.has_quantity) ? 1 : Math.max(1, Number($event.target.value))"
            x-bind:disabled="state.upsell.addonId && !state.allAddons.find(a => a.id == state.upsell.addonId)?.has_quantity"
            min="1"
            class="w-full border border-stone-300 bg-white px-3 py-2 text-center text-sm focus:border-stone-500 focus:outline-none disabled:bg-stone-100 disabled:text-stone-400"
          >
        </div>
        <x-shared.button
          type="button"
          x-on:click="submitUpsell()"
          x-bind:disabled="state.upsell.isLoading || !state.upsell.addonId"
          variant="charcoal"
          size="sm"
        >
          <x-slot:iconLeft>
            <i class="ri-add-line leading-none" aria-hidden="true"></i>
          </x-slot:iconLeft>
          <span x-text="state.upsell.isLoading ? 'Menambahkan...' : 'Tambah'"></span>
        </x-shared.button>
      </div>
    </div>
  </template>
  {{-- inline form upsell end --}}
</div>
{{-- addon items card end --}}
