@php
  use App\Domains\Booking\Enums\BookingStatus;
  use App\Domains\Payment\Enums\PaymentStatus;
  use App\Domains\Booking\Enums\PaymentScheme;
@endphp

<x-layouts.frontdoor.index title="Reservasi Berhasil">
  <x-slot:content>
    <div class="flex min-h-dvh items-center justify-center py-12">
      <div class="flex w-full max-w-2xl flex-col items-stretch border border-stone-300 p-6 sm:p-8 md:p-12">

        {{-- icon centang start --}}
        <div class="mb-6 flex justify-center">
          @if ($booking->booking_status === BookingStatus::PENDING)
            <div class="flex h-16 w-16 items-center justify-center border border-amber-600 text-amber-600">
              <i class="ri-time-line text-3xl" aria-hidden="true"></i>
            </div>
          @elseif ($booking->booking_status === BookingStatus::CANCELLED)
            <div class="flex h-16 w-16 items-center justify-center border border-red-600 text-red-600">
              <i class="ri-close-line text-3xl" aria-hidden="true"></i>
            </div>
          @else
            <div class="flex h-16 w-16 items-center justify-center border border-stone-900">
              <i class="ri-check-line text-3xl" aria-hidden="true"></i>
            </div>
          @endif
        </div>
        {{-- icon centang end --}}

        {{-- header start --}}
        <div class="mb-10 space-y-3 text-center">
          @if ($booking->booking_status === BookingStatus::PENDING)
            <h1 class="text-3xl font-bold tracking-tight text-amber-600">Menunggu Pembayaran</h1>
            <p class="font-light">Reservasi Anda telah dicatat. Kami sedang menunggu konfirmasi pembayaran dari sistem.
            </p>
            <p class="mt-2 text-sm italic text-stone-500">Jika Anda sudah membayar, harap tunggu beberapa saat dan
              refresh halaman ini.</p>
          @elseif ($booking->booking_status === BookingStatus::CANCELLED)
            <h1 class="text-3xl font-bold tracking-tight text-red-600">Reservasi Dibatalkan</h1>
            <p class="font-light">Waktu pembayaran telah habis atau pembayaran dibatalkan.</p>
          @else
            <h1 class="text-3xl font-bold tracking-tight">Reservasi Berhasil!</h1>
            <p class="font-light">Terima kasih, sesi foto Anda telah berhasil dijadwalkan.</p>
          @endif
        </div>
        {{-- header end --}}

        {{-- divider start --}}
        <div class="mb-8 border border-stone-300"></div>
        {{-- divider end --}}

        {{-- data section start --}}
        <div class="space-y-6 text-sm">
          <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-stone-500">Kode Booking</span>
            <span class="font-mono text-base font-bold text-stone-900 sm:text-right">{{ $booking->booking_code }}</span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-stone-500">Paket</span>
            <span class="font-medium text-stone-900 sm:max-w-[320px] sm:text-right">
              {{ $booking->package_name }} - {{ $booking->variant_name }}
            </span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-stone-500">Background</span>
            <span class="font-medium text-stone-900 sm:text-right">{{ $booking->background_name }}</span>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-stone-500">Waktu</span>
            <div class="font-medium sm:space-y-1 sm:text-right">
              <p>{{ $booking->formatted_date }}</p>
              <p class="text-stone-600 sm:text-stone-900">({{ $booking->formatted_time }})</p>
            </div>
          </div>

          <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <span class="text-xs font-semibold uppercase tracking-widest text-stone-500">Pelanggan</span>
            <div class="sm:space-y-1 sm:text-right">
              <p class="font-medium">{{ $booking->customer_name }}</p>
              <p class="text-xs text-stone-500">({{ $booking->customer_phone }})</p>
            </div>
          </div>
        </div>
        {{-- data section end --}}

        <!-- Divider line -->
        <div class="mb-8 mt-8 border border-stone-300"></div>

        {{-- payment info start --}}
        <div class="space-y-4">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <span class="mb-1 block text-xs font-bold uppercase tracking-widest text-stone-500">Total Pembayaran</span>
              <span class="font-serif text-3xl font-bold text-stone-900">
                {{ $booking->formatted_total_price }}
              </span>
            </div>
            <div>
              @if ($booking->booking_status === BookingStatus::PENDING)
                <span
                  class="inline-block select-none border border-amber-300 bg-amber-100 px-4 py-2 text-xs font-bold uppercase tracking-wider text-amber-700"
                >
                  Menunggu Pembayaran
                </span>
              @elseif ($booking->booking_status === BookingStatus::CANCELLED)
                <span
                  class="inline-block select-none border border-red-300 bg-red-100 px-4 py-2 text-xs font-bold uppercase tracking-wider text-red-700"
                >
                  Dibatalkan
                </span>
              @elseif ($booking->payment_scheme === PaymentScheme::DP)
                <span
                  class="inline-block select-none border border-stone-300 bg-stone-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-stone-500"
                >
                  DP 60% Terbayar ({{ $booking->formatted_dp_amount }})
                </span>
              @else
                <span
                  class="inline-block select-none border border-green-300 bg-green-100 px-4 py-2 text-xs font-bold uppercase tracking-wider text-green-700"
                >
                  Lunas Terbayar
                </span>
              @endif
            </div>
          </div>

          @if ($booking->payment_scheme === PaymentScheme::DP)
            <div class="border border-stone-300 bg-stone-200 p-2 text-center">
              <p class="text-sm italic leading-relaxed text-stone-500">
                "Sisa pembayaran sebesar
                {{ $booking->formatted_remaining_amount }}
                wajib dilunasi di studio setelah sesi foto selesai."
              </p>
            </div>
          @endif
        </div>
        {{-- payment info end --}}

        @if ($booking->payment_status === PaymentStatus::SETTLEMENT)
          {{-- notification note start --}}
          <div class="my-6 flex items-start gap-3 pr-12 text-xs text-stone-500">
            <i class="ri-whatsapp-line shrink-0 text-base text-stone-500" aria-hidden="true"></i>
            <p class="leading-relaxed">
              Detail reservasi dan bukti pembayaran telah otomatis dikirimkan ke nomor WhatsApp Anda.
            </p>
          </div>
          {{-- notification note start --}}
        @endif

        {{-- action buttons start --}}
        <div class="flex flex-col sm:flex-row gap-4 mt-4">
          <x-shared.button
            as="a"
            href="{{ route('frontdoor.dashboard.index') }}"
            variant="outline"
            value="Lihat Riwayat Pesanan"
            class="w-full sm:flex-1 text-center"
          />
          @if ($booking->payment_status === PaymentStatus::SETTLEMENT)
            <x-shared.button
              as="a"
              href="{{ route('payments.receipt', $booking->booking_code) }}"
              target="_blank"
              variant="dark"
              value="Unduh Bukti Reservasi (PDF)"
              class="w-full sm:flex-1 text-center"
            />
          @endif
        </div>
        {{-- action buttons end --}}
      </div>
    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
