<div class="max-w-5xl mx-auto px-6">
  <div class="text-center mb-12">
    <h2 class="text-3xl font-bold font-heading text-stone-900 mb-2">Layanan Tambahan</h2>
    <p class="text-stone-500 text-sm md:text-base font-medium">Pilih layanan ekstra (opsional) untuk memaksimalkan hasil
      sesi foto Anda</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <template x-for="addon in state.allAddons" :key="addon.id">
      <x-frontdoor.booking.addon-card />
    </template>
  </div>

  <div class="my-8 flex flex-col gap-12 sm:flex-row">
    <x-shared.button
      variant="outline"
      size="lg"
      value="Kembali"
      x-on:click="prevStep()"
      class="w-full"
    >
      <x-slot:iconLeft>
        <i class="ri-arrow-left-line"></i>
      </x-slot:iconLeft>
    </x-shared.button>
    <x-shared.button
      variant="dark"
      size="lg"
      value="Lihat Ringkasan Pesanan"
      x-on:click="nextStep()"
      class="w-full"
    >
      <x-slot:iconRight>
        <i class="ri-arrow-right-line"></i>
      </x-slot:iconRight>
    </x-shared.button>
  </div>
</div>
