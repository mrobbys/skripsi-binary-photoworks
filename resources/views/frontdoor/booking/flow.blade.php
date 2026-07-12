<x-layouts.frontdoor.index
  title="Booking Sesi Foto — {{ $package->name }}"
  jsModule="frontdoor/booking/Booking">

  <x-slot:heads>
    {{-- midtrans --}}
    <script type="text/javascript" src="{{ config('midtrans.snap_js_url') }}"
      data-client-key="{{ config('midtrans.client_key') }}"></script>
  </x-slot:heads>

  <x-slot:content>
    <div
      x-data="Booking"
      x-init="state.allVariants = {{ Js::from($variants) }};
      state.allAddons = {{ Js::from($addons) }};
      state.allBackgrounds = {{ Js::from($backgrounds) }};
      state.activeDays = {{ Js::from($activeDays) }};
      state.packageName = {{ Js::from($package->name) }};"
      x-cloak>
      {{-- kembali ke katalog start --}}
      <div class="w-fit h-fit whitespace-nowrap">
        <x-shared.button as="a" href="{{ route('frontdoor.services.index') }}"
          class="mb-8 group text-stone-500 hover:bg-stone-100 hover:text-stone-800">
          <x-slot:iconLeft>
            <i class="ri-arrow-left-line group-hover:-translate-x-1 transition-transform"></i>
          </x-slot:iconLeft>
          Kembali ke Katalog
        </x-shared.button>
      </div>
      {{-- kembali ke katalog end --}}

      {{-- step indicator start --}}
      <div x-show="!state.selectedVariant?.is_whatsapp_only">
        <x-frontdoor.booking.step-indicator />
      </div>
      {{-- step indicator start --}}

      {{-- multi step form start --}}
      <div class="mx-auto py-10">
        <template x-if="state.currentStep === 1">
          @include('frontdoor.booking.step-1-variant', ['package' => $package])
        </template>
        <template x-if="state.currentStep === 2">
          @include('frontdoor.booking.step-2-schedule')
        </template>
        <template x-if="state.currentStep === 3">
          @include('frontdoor.booking.step-3-addons')
        </template>
        <template x-if="state.currentStep === 4">
          @include('frontdoor.booking.step-4-summary')
        </template>
      </div>
      {{-- multi step form end --}}
    </div>
  </x-slot:content>

</x-layouts.frontdoor.index>
