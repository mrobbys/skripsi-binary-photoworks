<div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16" x-init="if (!state.selectedVariantId && state.allVariants.length > 0) selectVariant(state.allVariants[0])">
  {{-- section kiri start --}}
  <div>
    <div class="aspect-4/5 overflow-hidden bg-stone-100">
      <img src="https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800"
        alt="{{ $package->name }}" class="w-full h-full object-cover select-none pointer-events-none">
    </div>

    <div class="mt-8">
      <h3 class="font-semibold font-heading italic tracking-wide text-stone-900 text-sm mb-4">
        Keterangan :
      </h3>
      @if ($package->features->count() > 0)
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
          @foreach ($package->features as $feature)
            <li class="flex items-start gap-2.5 text-sm text-stone-600">
              <span class="size-1.5 bg-stone-400 mt-1.5 shrink-0"></span>
              <span>{{ $feature->description }}</span>
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-sm text-stone-400 italic">Tidak ada keterangan paket.</p>
      @endif
    </div>
  </div>
  {{-- section kiri end --}}

  {{-- section kanan start --}}
  <div class="space-y-10">
    <div>
      <h1 class="text-3xl md:text-4xl font-heading font-bold text-stone-900 mb-4">
        {{ $package->name }}
      </h1>
      {{-- TODO: jangan lupa dibuat field baru di package (description) --}}
      <p class="text-stone-600 text-sm md:text-base leading-relaxed">
        Abadikan momen berharga Anda dengan sesi fotografi wisuda profesional kami. Pilih paket dan latar belakang yang
        Anda inginkan untuk pengalaman yang lebih personal.
      </p>
    </div>

    {{-- pilih variant start --}}
    <div>
      <h3 class="text-sm font-semibold uppercase tracking-widest text-stone-900 mb-4">Pilih Paket</h3>
      <div class="space-y-3">
        @foreach ($variants as $variant)
          <x-frontdoor.booking.variant-radio :variant="$variant" />
        @endforeach
      </div>
    </div>
    {{-- pilih variant start --}}

    {{-- pilih background start --}}
    <template
      x-if="state.selectedVariantId && !state.selectedVariant?.is_whatsapp_only && state.allBackgrounds && state.allBackgrounds.length > 0">
      <div>
        <h3 class="text-sm font-semibold uppercase tracking-widest text-stone-900 mb-4">Pilih Background</h3>
        <div class="flex flex-wrap gap-4">
          <template x-for="bg in state.allBackgrounds" :key="bg.id">
            <x-frontdoor.booking.background-thumb />
          </template>
        </div>
      </div>
    </template>
    {{-- pilih background end --}}

    <div class="pt-2">
      {{-- tombol lanjut reservasi ke jadwal start --}}
      <template x-if="!state.selectedVariant?.is_whatsapp_only">
        <x-shared.button variant="primary" class="w-full py-4"
          x-bind:disabled="!state.selectedVariantId || !state.selectedBackgroundId" x-on:click="nextStep()">
          Lanjutkan ke Jadwal Sesi
        </x-shared.button>
      </template>
      {{-- tombol lanjut reservasi ke jadwal end --}}

      {{-- button paket variant wa only start --}}
      <template x-if="state.selectedVariant?.is_whatsapp_only">
        <x-shared.button as="a" target="_blank" variant="primary" size="lg"
          x-bind:disabled="!state.selectedVariantId"
          x-bind:href="state.selectedVariant ?
              `https://wa.me/6281234567890?text=${encodeURIComponent('Halo Admin, saya ingin reservasi paket {{ $package->name }} - ' + state.selectedVariant.name)}` :
              '#'">
          <x-slot:iconLeft>
            <i class="ri-whatsapp-line text-lg"></i>
          </x-slot:iconLeft>
          Reservasi via WhatsApp
        </x-shared.button>
      </template>
      {{-- button paket variant wa only end --}}

      <p class="text-center text-stone-500 text-sm mt-4">Silakan pilih paket <span
          x-show="!state.selectedVariant?.is_whatsapp_only">dan background </span>untuk melanjutkan</p>
    </div>
  </div>
  {{-- section kanan end --}}
</div>
