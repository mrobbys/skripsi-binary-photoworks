@php
  if (request()->routeIs('backdoor.session-schedule.*')) {
      $backUrl = route('backdoor.session-schedule.list');
      $backLabel = 'Jadwal Sesi';
  } else {
      $backUrl = route('backdoor.booking-management.index');
      $backLabel = 'Manajemen Pemesanan';
  }

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => $backLabel, 'url' => $backUrl],
      ['label' => 'Detail Booking', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Booking — {{ $booking->booking_code }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/booking-management/show/Show"
>

  <x-slot:content>
    <div
      x-data="Show"
      x-init="mount({{ $booking->id }}, '{{ $booking->booking_code }}', {{ Js::from($availableAddons) }})"
      x-cloak
      class="pb-16"
    >

      {{-- skeleton loading start --}}
      <div x-show="state.isPageLoading">
        <x-skeleton.backdoor-booking-detail />
      </div>
      {{-- skeleton loading end --}}

      <div
        x-show="!state.isPageLoading"
        x-cloak
      >

        {{-- header section start --}}
        <x-backdoor.shared.page-header-back
          :href="$backUrl"
          :backLabel="'Kembali Ke ' . $backLabel"
          class="pb-6"
        >
          <span x-text="state.bookingCode"></span>

          <x-slot:badge>
            <x-shared.badge
              alpine="state.booking?.status === 'Lunas' || state.booking?.status === 'Selesai'"
              variant="lime"
              size="sm"
              x-text="state.booking?.status"
            />
            <x-shared.badge
              alpine="state.booking?.status === 'DP Terbayar'"
              variant="secondary"
              size="sm"
              x-text="state.booking?.status"
            />
            <x-shared.badge
              alpine="state.booking?.status === 'Menunggu'"
              variant="warning"
              size="sm"
              x-text="state.booking?.status"
            />
            <x-shared.badge
              alpine="state.booking?.status === 'Batal'"
              variant="danger"
              size="sm"
              x-text="state.booking?.status"
            />
          </x-slot:badge>

          <x-slot:subcontent>
            <span class="mt-2 sm:mt-0 text-sm"
                x-text="state.booking?.created_at ? 'Dibuat pada ' + new Date(state.booking.created_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}).replace(/\./g, ':') + ' WITA' : '...'"
              ></span>
          </x-slot:subcontent>
        </x-backdoor.shared.page-header-back>
        {{-- header section end --}}

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          {{-- kolom kiri: informasi sesi & add-ons start --}}
          <div class="space-y-5 lg:col-span-2">
            {{-- informasi klien start --}}
            <x-backdoor.booking-management.show.client-info />
            {{-- informasi klien end --}}

            {{-- detail sesi start --}}
            <x-backdoor.booking-management.show.session-details />
            {{-- detail sesi end --}}

            {{-- layanan tambahan start --}}
            <x-backdoor.booking-management.show.addon-items />
            {{-- layanan tambahan end --}}
          </div>
          {{-- kolom kiri: informasi sesi & add-ons end --}}

          {{-- kolom kanan: keuangan, riwayat, & gdrive start --}}
          <div class="space-y-4">
            {{-- ringkasan pembayaran start --}}
            <x-backdoor.booking-management.show.financial-summary />
            {{-- ringkasan pembayaran end --}}

            {{-- riwayat transaksi start --}}
            <x-backdoor.booking-management.show.payment-history />
            {{-- riwayat transaksi end --}}

            @can('booking-management-update')
              {{-- tombol tandai lunas start --}}
              <template
                x-if="
                  (state.booking?.status === 'DP Terbayar' || state.booking?.status === 'Lunas') &&
                  (Number(state.booking?.total_price || 0) > Number(state.summary?.net_paid || 0))
                "
              >
                <x-shared.button
                  type="button"
                  x-on:click="settle()"
                  variant="charcoal"
                  size="md"
                  class="w-full"
                  value="Tandai Lunas"
                >
                  <x-slot:iconLeft>
                    <i class="ri-checkbox-circle-line leading-none" aria-hidden="true"></i>
                  </x-slot:iconLeft>
                </x-shared.button>
              </template>
              {{-- tombol tandai lunas end --}}

              {{-- form link google drive start --}}
              <x-backdoor.booking-management.show.gdrive-form />
              {{-- form link google drive end --}}
            @endcan
          </div>
          {{-- kolom kanan: keuangan, riwayat, & gdrive end --}}
        </div>

      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
