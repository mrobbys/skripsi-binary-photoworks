<x-layouts.frontdoor.index
  title="Booking Sesi Foto — {{ $package->name }}"
  js-module="booking/Booking">

  <x-slot:content>
    <div
      x-data="Booking"
      x-init="state.allVariants = {{ Js::from($variants) }};
      state.allAddons = {{ Js::from($addons) }};
      state.allBackgrounds = {{ Js::from($backgrounds) }};
      state.activeDays = {{ Js::from($activeDays) }};"
      x-cloak
      class="p-6">
      <div class="w-fit h-fit whitespace-nowrap">
        <x-shared.button as="a" href="{{ route('frontdoor.services.index') }}" variant="ghost"
          class="mb-8 group text-stone-500">
          <x-slot:iconLeft>
            <i class="ri-arrow-left-line group-hover:-translate-x-1 transition-transform"></i>
          </x-slot:iconLeft>
          Kembali ke Layanan
        </x-shared.button>
      </div>

      {{-- step indicator start --}}
      <x-frontdoor.booking.step-indicator />
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

  <x-slot:scripts>
    {{-- midtrans --}}
    <script src="https://midtrans.com"
      data-client-key="{{ config('midtrans.client_key') }}"></script>
  </x-slot:scripts>

</x-layouts.frontdoor.index>
