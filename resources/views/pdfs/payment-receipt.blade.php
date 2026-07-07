<x-layouts.pdf
  :title="'Receipt-' . $formattedData->booking_code">
  <div class="max-w-4xl mx-auto p-10">
    {{-- header section start --}}
    <div class="flex justify-between items-start border-b border-stone-200 pb-8 mb-8">
      {{-- section left start --}}
      <div>
        <img src="{{ public_path('assets/binary-logo/binary-logo-text-black.png') }}" alt="Binary Photoworks"
          class="h-10 object-contain">
      </div>
      {{-- section left end --}}

      {{-- section right start --}}
      <div class="text-right">
        <h1 class="text-3xl font-bold tracking-tight text-stone-900">KUITANSI PEMBAYARAN</h1>
        <div class="mt-2 text-sm text-stone-600">
          <p>Kode Booking: <span class="font-medium text-stone-900">{{ $formattedData->booking_code }}</span></p>
          <p>Order ID: <span class="font-medium text-stone-900">{{ $formattedData->order_id }}</span></p>
          <p>Tanggal Bayar: <span class="font-medium text-stone-900">{{ $formattedData->pay_date }}</span></p>
          <p>Status: <span class="font-bold tracking-wider text-stone-900">{{ $formattedData->status }}</span></p>
        </div>
      </div>
      {{-- section right end --}}
    </div>
    {{-- header section end --}}

    {{-- data klien start --}}
    <div class="mb-10">
      <h2 class="text-xs font-bold tracking-widest text-stone-500 uppercase mb-2">Ditagihkan Kepada</h2>
      <div class="text-sm">
        <p class="font-bold text-stone-900 text-base">{{ $formattedData->client_name }}</p>
        <p class="text-stone-600">{{ $formattedData->client_email }}</p>
        <p class="text-stone-600">{{ $formattedData->client_phone ?? '-' }}</p>
      </div>
    </div>
    {{-- data klien end --}}

    {{-- table section start --}}
    <div class="mb-10">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b-2 border-stone-900 text-stone-900">
            <th class="py-3 pr-4 font-bold uppercase tracking-wider text-xs">Rincian Layanan</th>
            <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Jumlah</th>
            <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-right">Harga Satuan</th>
            <th class="py-3 pl-4 font-bold uppercase tracking-wider text-xs text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
          {{-- main package start --}}
          <tr>
            <td class="py-4 pr-4">
              <p class="font-medium text-stone-900">{{ $formattedData->package_name }}</p>
              <p class="text-xs text-stone-500 mt-1">Booking Paket</p>
            </td>
            <td class="py-4 px-4 text-center">1</td>
            <td class="py-4 px-4 text-right">{{ $formattedData->package_price }}</td>
            <td class="py-4 pl-4 text-right text-stone-900">{{ $formattedData->package_price }}</td>
          </tr>
          {{-- main package end --}}

          {{-- addons (jika ada) start --}}
          @foreach ($formattedAddons as $addon)
            <tr>
              <td class="py-4 pr-4">
                <p class="font-medium text-stone-900">{{ $addon->name }}</p>
                <p class="text-xs text-stone-500 mt-1">Layanan Tambahan</p>
              </td>
              <td class="py-4 px-4 text-center">{{ $addon->quantity }}</td>
              <td class="py-4 px-4 text-right">{{ $addon->price }}</td>
              <td class="py-4 pl-4 text-right text-stone-900">{{ $addon->amount }}</td>
            </tr>
          @endforeach
          {{-- addons (jika ada) end --}}
        </tbody>
      </table>
    </div>
    {{-- table section end --}}

    {{-- totals section start --}}
    <div class="flex justify-end mb-16 text-sm">
      <div class="w-1/2 md:w-1/2">
        <div class="flex justify-between py-2 border-b border-stone-200">
          <span class="text-stone-600">Total Harga Reservasi</span>
          <span class="text-stone-900 font-medium">{{ $formattedData->total_price }}</span>
        </div>
        <div class="flex justify-between py-2">
          <span class="text-stone-600">Metode Pembayaran</span>
          <span class="text-stone-900 font-medium uppercase">{{ $formattedData->payment_type }}</span>
        </div>
        <div class="flex justify-between py-3 font-bold text-base text-stone-900 border-t border-stone-900 mt-2">
          <span>Jumlah yang Dibayar</span>
          <span>{{ $formattedData->amount_paid }}</span>
        </div>
        @if ($formattedData->payment_scheme === 'dp' && $formattedData->payment_purpose === 'dp')
          <div class="flex justify-between py-2 text-xs text-stone-500">
            <span>Sisa Pelunasan (Di Bayar di Kasir)</span>
            <span>{{ $formattedData->remaining }}</span>
          </div>
        @endif
      </div>
    </div>
    {{-- totals section end --}}

    <!-- Footer Section -->
    <div class="border-t border-stone-200 pt-8 text-center text-xs text-stone-500 space-y-2">
      <p>Terima kasih telah mempercayai {{ config('studio.nama_studio') }}.</p>
      <p>Jika Anda memiliki pertanyaan mengenai pembayaran ini, silakan hubungi kami.</p>
      <a href="https://wa.me/{{ $formattedData->studio_whatsapp }}" class="block font-medium text-stone-700">
        WhatsApp:
        <span
          class="underline">+{{ $formattedData->studio_whatsapp }}
        </span>
      </a>
    </div>
  </div>
</x-layouts.pdf>
