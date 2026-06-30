<div>
  <h3 class="text-xl font-bold text-stone-900 mb-2">Layanan Tambahan</h3>
  <p class="text-sm text-stone-600 mb-8">Opsional — bisa dikosongkan.</p>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <template x-for="addon in state.allAddons" :key="addon.id">
      <x-frontdoor.booking.addon-card />
    </template>
  </div>

  <div class="mt-10 flex gap-3">
    <x-shared.button variant="ghost" x-on:click="prevStep()">
      <i class="ri-arrow-left-line mr-1"></i> Kembali
    </x-shared.button>
    <x-shared.button variant="primary" class="flex-1" x-on:click="nextStep()">
      Lihat Ringkasan Pesanan
      <i class="ri-arrow-right-line ml-2"></i>
    </x-shared.button>
  </div>
</div>
