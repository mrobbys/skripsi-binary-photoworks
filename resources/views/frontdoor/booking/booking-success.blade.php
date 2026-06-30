<x-layouts.frontdoor.index title="Reservasi Berhasil — Binary Photoworks">
  <x-slot:content>
    <div class="max-w-2xl mx-auto px-6 py-20">
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-50 border border-green-300 mb-6">
          <i class="ri-checkbox-circle-fill text-3xl text-green-600"></i>
        </div>
        <h1 class="text-3xl font-bold text-stone-900">Reservasi Berhasil!</h1>
        <p class="mt-2 text-stone-600">Slot jadwal Anda telah terkunci aman di sistem kami.</p>
      </div>

      <div class="border border-stone-200">
        <div class="p-5 border-b border-stone-200 flex items-center justify-between">
          <span class="text-sm text-stone-600">Kode Booking</span>
          <span class="font-mono font-bold text-stone-900 text-lg">{{ $booking->booking_code }}</span>
        </div>
        <div class="p-5 space-y-3 text-sm">
          <div class="flex justify-between">
            <span class="text-stone-600">Paket</span>
            <span class="font-medium">{{ $booking->packageVariant->name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-stone-600">Tanggal Sesi</span>
            <span class="font-medium">{{ $booking->booking_date->translatedFormat('d F Y') }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-stone-600">Waktu Sesi</span>
            <span class="font-medium">
              {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} –
              {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WITA
            </span>
          </div>
          <div class="flex justify-between border-t border-stone-200 pt-3 font-bold text-base">
            <span class="text-stone-600">Total Dibayar</span>
            <span class="text-stone-900">
              Rp {{ number_format($booking->payments->first()?->amount ?? 0, 0, ',', '.') }}
            </span>
          </div>
        </div>

        @if ($booking->payment_scheme === 'dp')
          <div class="bg-amber-50 border-t border-amber-200 p-4">
            <p class="text-xs text-amber-800">
              <i class="ri-information-line mr-1"></i>
              Sisa tagihan 40% (Rp
              {{ number_format($booking->total_price - ($booking->payments->first()?->amount ?? 0), 0, ',', '.') }})
              dilunasi di kasir studio pada hari sesi foto.
            </p>
          </div>
        @endif
      </div>

      <div class="mt-8 flex flex-col sm:flex-row gap-3">
        <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}" variant="outline"
          class="flex-1 text-center">
          <i class="ri-calendar-check-line mr-2"></i> Lihat Riwayat Pesanan
        </x-shared.button>
        <x-shared.button variant="primary" class="flex-1">
          <i class="ri-download-2-line mr-2"></i> Unduh Bukti Reservasi
        </x-shared.button>
      </div>
    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
