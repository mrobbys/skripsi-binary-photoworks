@php
  $packageImage = $package->getFirstMediaUrl('package-image', 'webp') ?: $package->getFirstMediaUrl('package-image');
@endphp

<div
  class="grid grid-cols-1 gap-12 lg:grid-cols-2"
  x-init="if (!state.selectedVariantId && state.allVariants.length > 0) selectVariant(state.allVariants[0])"
>
  {{-- section kiri start --}}
  <div>
    <div class="aspect-4/5 w-full overflow-hidden border border-stone-300 bg-stone-200">
      @if ($packageImage)
        <img
          src="{{ $packageImage }}"
          alt="{{ $package->name }}"
          class="pointer-events-none h-full w-full select-none object-cover"
        >
      @else
        <div class="flex h-full w-full flex-col items-center justify-center gap-2 p-4 text-center text-stone-400">
          <i
            class="ri-image-line text-4xl"
            aria-hidden="true"
          ></i>
          <span class="text-base font-semibold">Belum ada gambar</span>
        </div>
      @endif
    </div>

    <div class="mt-8">
      <h3 class="font-heading mb-4 text-sm font-semibold italic tracking-wide text-stone-900">
        Keterangan :
      </h3>
      @if ($package->features->count() > 0)
        <ul class="grid grid-cols-1 gap-x-4 gap-y-3 md:grid-cols-2">
          @foreach ($package->features as $feature)
            <li class="flex items-start gap-2.5 text-sm text-stone-600">
              <span class="mt-1.5 size-1.5 shrink-0 bg-stone-400"></span>
              <span>{{ $feature->description }}</span>
            </li>
          @endforeach
        </ul>
      @else
        <p class="text-sm italic text-stone-400">Tidak ada keterangan paket.</p>
      @endif
    </div>
  </div>
  {{-- section kiri end --}}

  {{-- section kanan start --}}
  <div class="space-y-10">
    <div>
      <h1 class="font-heading mb-4 text-3xl font-bold text-stone-900 md:text-4xl">
        {{ $package->name }}
      </h1>
      <p class="text-sm leading-relaxed text-stone-600 md:text-base">
        {{ $package->description ?? '' }}
      </p>
    </div>

    {{-- pilih variant start --}}
    <div>
      <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-stone-900">Pilih Paket</h3>
      <div class="space-y-3">
        @foreach ($variants as $variant)
          <x-frontdoor.booking.variant-radio :variant="$variant" />
        @endforeach
      </div>
    </div>
    {{-- pilih variant start --}}

    {{-- pilih background start --}}
    <template
      x-if="state.selectedVariantId && !state.selectedVariant?.is_whatsapp_only && state.allBackgrounds && state.allBackgrounds.length > 0"
    >
      <div>
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-stone-900">Pilih Background</h3>
        <div class="scrollbar-none flex snap-x snap-mandatory items-center gap-4 overflow-x-auto p-3">
          <template
            x-for="bg in state.allBackgrounds"
            :key="bg.id"
          >
            <x-frontdoor.booking.background-thumb />
          </template>
        </div>
      </div>
    </template>
    {{-- pilih background end --}}

    <div class="pt-2">
      {{-- tombol lanjut reservasi ke jadwal start --}}
      <template x-if="!state.selectedVariant?.is_whatsapp_only">
        <x-shared.button
          variant="dark"
          size="lg"
          value="Lanjutkan ke Jadwal Sesi"
          x-bind:disabled="!state.selectedVariantId || !state.selectedBackgroundId"
          x-on:click="nextStep()"
          class="w-full"
        />
      </template>
      {{-- tombol lanjut reservasi ke jadwal end --}}

      {{-- button paket variant wa only start --}}
      <template x-if="state.selectedVariant?.is_whatsapp_only">
        <x-shared.button
          as="a"
          target="_blank"
          variant="dark"
          size="lg"
          value="Reservasi via WhatsApp"
          x-bind:disabled="!state.selectedVariantId"
          x-bind:href="state.selectedVariant ?
              `https://wa.me/6281234567890?text=${encodeURIComponent('Halo Admin, saya ingin reservasi paket ' + state.packageName + ' - ' + state.selectedVariant.name)}` :
              '#'"
          class="w-full"
        >
          <x-slot:iconLeft>
            <i
              class="ri-whatsapp-line text-lg"
              aria-hidden="true"
            ></i>
          </x-slot:iconLeft>
        </x-shared.button>
      </template>
      {{-- button paket variant wa only end --}}

      <p class="mt-4 text-center text-sm text-stone-500">Silakan pilih paket <span
          x-show="!state.selectedVariant?.is_whatsapp_only"
        >dan background </span>untuk melanjutkan</p>
    </div>
  </div>
  {{-- section kanan end --}}
</div>
