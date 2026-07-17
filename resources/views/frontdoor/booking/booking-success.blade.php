<x-layouts.frontdoor.index title="Reservasi Berhasil — Binary Photoworks">
  <x-slot:content>
    <div class="min-h-dvh flex items-center justify-center py-12 px-6">
      <div
        class="w-full max-w-2xl border border-stone-300 p-12 flex flex-col items-stretch">

        {{-- icon centang start --}}
        <div class="flex justify-center mb-6">
          @if ($booking->status === \App\Domains\Booking\Enums\BookingStatus::PENDING)
            <div class="w-16 h-16 border border-amber-600 flex items-center justify-center text-amber-600">
              <i class="ri-time-line text-3xl"></i>
            </div>
          @elseif ($booking->status === \App\Domains\Booking\Enums\BookingStatus::CANCELLED)
            <div class="w-16 h-16 border border-red-600 flex items-center justify-center text-red-600">
              <i class="ri-close-line text-3xl"></i>
            </div>
          @else
            <div class="w-16 h-16 border border-stone-900 flex items-center justify-center">
              <i class="ri-check-line text-3xl"></i>
            </div>
          @endif
        </div>
        {{-- icon centang end --}}

        {{-- header start --}}
        <div class="text-center mb-10 space-y-3">
          @if ($booking->status === \App\Domains\Booking\Enums\BookingStatus::PENDING)
            <h1 class="text-3xl font-bold tracking-tight text-amber-600">Menunggu Pembayaran</h1>
            <p class="font-light">Reservasi Anda telah dicatat. Kami sedang menunggu konfirmasi pembayaran dari sistem.</p>
            <p class="text-sm text-stone-500 italic mt-2">Jika Anda sudah membayar, harap tunggu beberapa saat dan refresh halaman ini.</p>
          @elseif ($booking->status === \App\Domains\Booking\Enums\BookingStatus::CANCELLED)
            <h1 class="text-3xl font-bold tracking-tight text-red-600">Reservasi Dibatalkan</h1>
            <p class="font-light">Waktu pembayaran telah habis atau pembayaran dibatalkan.</p>
          @else
            <h1 class="text-3xl font-bold tracking-tight">Reservasi Berhasil!</h1>
            <p class="font-light">Terima kasih, sesi foto Anda telah berhasil dijadwalkan.</p>
          @endif
        </div>
        {{-- header end --}}

        {{-- divider start --}}
        <div class="border border-stone-300 mb-8"></div>
        {{-- divider end --}}

        {{-- data section start --}}
        <div class="space-y-6 text-sm">
          <div class="flex justify-between items-start">
            <span
              class="text-xs font-semibold tracking-widest text-stone-500 uppercase">Kode Booking</span>
            <span
              class="font-mono font-bold text-stone-900 text-base">{{ $booking->booking_code }}</span>
          </div>

          <div class="flex justify-between items-start">
            <span
              class="text-xs font-semibold tracking-widest text-stone-500 uppercase">Paket</span>
            <span class="font-medium text-stone-900 text-right max-w-[320px]">
              {{ $booking->package_name }} -
              {{ $booking->variant_name }}
            </span>
          </div>

          <div class="flex justify-between items-start">
            <span
              class="text-xs font-semibold tracking-widest text-stone-500 uppercase">Background</span>
            <span
              class="font-medium text-stone-900">{{ $booking->background_name }}</span>
          </div>

          <div class="flex justify-between items-center">
            <span
              class="text-xs font-semibold tracking-widest text-stone-500 uppercase">Waktu</span>
            <div class="text-right font-medium space-y-1">
              <p>
                {{ $booking->formatted_date }}
              </p>
              <p>
                ({{ $booking->formatted_time }})
              </p>
            </div>
          </div>

          <div class="flex justify-between items-center">
            <span
              class="text-xs font-semibold tracking-widest text-stone-500 uppercase">Pelanggan</span>
            <div class="text-right space-y-1">
              <p class="font-medium">{{ $booking->customer_name }}</p>
              <p class="text-xs text-stone-500">
                ({{ $booking->customer_phone }})</p>
            </div>
          </div>
        </div>
        {{-- data section end --}}

        <!-- Divider line -->
        <div class="border-t border-stone-300 mt-8 mb-8"></div>

        {{-- payment info start --}}
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <span
                class="text-xs font-bold tracking-widest text-stone-500 uppercase block mb-1">Total
                Pembayaran</span>
              <span class="font-serif text-2xl font-bold text-stone-900">
                {{ $booking->formatted_total_price }}
              </span>
            </div>
            <div>
              @if ($booking->status === \App\Domains\Booking\Enums\BookingStatus::PENDING)
                <span
                  class="inline-block bg-amber-100 border border-amber-300 text-xs font-bold tracking-wider uppercase px-4 py-2 text-amber-700 select-none">
                  Menunggu Pembayaran
                </span>
              @elseif ($booking->status === \App\Domains\Booking\Enums\BookingStatus::CANCELLED)
                <span
                  class="inline-block bg-red-100 border border-red-300 text-xs font-bold tracking-wider uppercase px-4 py-2 text-red-700 select-none">
                  Dibatalkan
                </span>
              @elseif ($booking->payment_scheme === \App\Domains\Booking\Enums\PaymentScheme::DP)
                <span
                  class="inline-block bg-stone-200 border border-stone-300 text-xs font-bold tracking-wider uppercase px-4 py-2 text-stone-500 select-none">
                  DP 60% Terbayar ({{$booking->formatted_dp_amount}})
                </span>
              @else
                <span
                  class="inline-block bg-green-100 border border-green-300 text-xs font-bold tracking-wider uppercase px-4 py-2 text-green-700 select-none">
                  Lunas Terbayar
                </span>
              @endif
            </div>
          </div>

          @if ($booking->payment_scheme === \App\Domains\Booking\Enums\PaymentScheme::DP)
            <div class="bg-stone-200 border border-stone-300 p-2 text-center">
              <p class="text-sm italic text-stone-500 leading-relaxed">
                "Sisa pembayaran sebesar
                {{ $booking->formatted_remaining_amount }}
                wajib dilunasi di studio setelah sesi foto selesai."
              </p>
            </div>
          @endif
        </div>
        {{-- payment info end --}}

        {{-- notification note start --}}
        <div class="flex items-start gap-3 my-6 pr-12 text-xs text-stone-500">
          <i class="ri-whatsapp-line text-base text-stone-500 shrink-0"></i>
          <p class="leading-relaxed">
            Detail reservasi dan bukti pembayaran telah otomatis dikirimkan ke nomor WhatsApp Anda.
          </p>
        </div>
        {{-- notification note start --}}

        {{-- action buttons start --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}"
            size="custom"
            class="w-full border border-stone-300 bg-transparent text-stone-500 px-6 py-3 text-sm font-semibold! hover:bg-stone-200 transition-colors duration-150">
            Lihat Riwayat Pesanan
          </x-shared.button>
          <x-shared.button as="a"
            href="{{ route('payments.receipt', $booking->order_id) }}"
            target="_blank"
            size="custom"
            class="w-full border border-stone-500 bg-stone-500 text-stone-50 px-6 py-3 text-sm font-semibold! hover:bg-stone-600 transition-colors duration-150">
            Unduh Bukti Reservasi (PDF)
          </x-shared.button>
        </div>
        {{-- action buttons end --}}
      </div>
    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
