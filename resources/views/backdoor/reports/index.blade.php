@php
  use App\Domains\Booking\Enums\BookingStatus;

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Laporan', 'url' => ''],
  ];

  $bookingStatusOptions = collect(BookingStatus::cases())
      ->pluck('value', 'value')
      ->toArray();
@endphp

<x-layouts.backdoor.index
  title="Laporan"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/reports/Index"
>

  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-stone-900">Laporan</h1>
          <p class="mt-1 text-sm text-stone-500">Pilih jenis laporan yang Anda inginkan.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">

        {{-- laporan rekapitulasi pendapatan transaksi start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Pendapatan Transaksi"
          description="Menghitung total pendapatan dari payment berdasarkan status = SETTLEMENT."
          :action="route('backdoor.reports.pendapatan.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
        </x-backdoor.reports.card>
        {{-- laporan rekapitulasi pendapatan transaksi end --}}

        {{-- laporan rekapitulasi pemesanan start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Pemesanan"
          description="Menampilkan seluruh riwayat booking berdasarkan tanggal (created_at) dan status booking."
          :action="route('backdoor.reports.pemesanan.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
          <x-backdoor.reports.select-input
            name="status"
            label="STATUS PEMESANAN"
            :options="$bookingStatusOptions"
          />
        </x-backdoor.reports.card>
        {{-- laporan rekapitulasi pemesanan end --}}

        {{-- laporan jadwal operasional harian start --}}
        <x-backdoor.reports.card
          title="Laporan Jadwal Operasional Harian"
          description="Lembar panduan kerja (job sheet) kru fotografer dan admin studio untuk 1 hari spesifik."
          :action="route('backdoor.reports.jadwal-harian.pdf')"
        >
          <x-backdoor.reports.date-input
            name="date"
            label="TANGGAL PELAKSANAAN"
          />
        </x-backdoor.reports.card>
        {{-- laporan jadwal operasional harian end --}}

        {{-- laporan rekapitulasi performa hari start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Performa Hari"
          description="Mengukur tingkat kepadatan operasional untuk mengetahui hari dengan volume reservasi tertinggi."
          :action="route('backdoor.reports.performa-hari.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
        </x-backdoor.reports.card>
        {{-- laporan rekapitulasi performa hari end --}}

        {{-- laporan rekapitulasi ulasan pelanggan start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Ulasan Pelanggan"
          description="Bahan evaluasi internal tim untuk memantau nilai kepuasan dan ulasan pelanggan studio."
          :action="route('backdoor.reports.ulasan-pelanggan.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
        </x-backdoor.reports.card>
        {{-- laporan rekapitulasi ulasan pelanggan end --}}

        {{-- laporan data klien start --}}
        <x-backdoor.reports.card
          title="Laporan Data Klien"
          description="Daftar identitas pelanggan baru yang terdaftar di sistem berdasarkan tanggal akun dibuat."
          :action="route('backdoor.reports.data-klien.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
        </x-backdoor.reports.card>
        {{-- laporan data klien end --}}

        {{-- laporan data paket (katalog master) start --}}
        <x-backdoor.reports.card
          title="Laporan Data Paket (Katalog Master)"
          description="Menyajikan katalog data master harga, varian paket, dan durasi studio yang sedang aktif."
          :action="route('backdoor.reports.data-paket.pdf')"
        >
          <div class="py-2 text-xs italic text-stone-500">
            Dokumen ini mengekspor seluruh data master katalog paket yang aktif secara langsung (Format Landscape).
          </div>
        </x-backdoor.reports.card>
        {{-- laporan data paket (katalog master) end --}}

        {{-- laporan rekapitulasi jadwal pemotretan start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Jadwal Pemotretan"
          description="Merangkum kepadatan pemesanan studio jangka panjang untuk manajemen stok background."
          :action="route('backdoor.reports.jadwal-pemotretan.pdf')"
        >
          <x-backdoor.reports.date-input
            name="start_date"
            label="TANGGAL AWAL"
          />
          <x-backdoor.reports.date-input
            name="end_date"
            label="TANGGAL AKHIR"
          />
        </x-backdoor.reports.card>
        {{-- laporan rekapitulasi jadwal pemotretan end --}}

      </div>

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
