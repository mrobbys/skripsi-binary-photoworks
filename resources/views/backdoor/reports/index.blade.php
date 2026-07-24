@php
  use App\Domains\Booking\Enums\BookingStatus;
  use App\Domains\Booking\Models\Booking;
  use Carbon\Carbon;

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Laporan', 'url' => ''],
  ];

  // Untuk option select status
  $bookingStatusOptions = collect(BookingStatus::cases())->pluck('value', 'value')->toArray();

  // Untuk option select tahun
  $minYear = Booking::min('booking_date') ? Carbon::parse(Booking::min('booking_date'))->year : now()->year;
  $tahunOptions = collect(range($minYear, now()->year))
      ->mapWithKeys(fn($y) => [$y => (string) $y])
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
          description="Menghitung total pendapatan dari payment berdasarkan tanggal (pay_date) dan status = SETTLEMENT."
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

        {{-- laporan pendapatan tahunan start --}}
        <x-backdoor.reports.card
          title="Laporan Pendapatan Tahunan"
          description="Total pendapatan dan sesi foto per bulan berdasarkan filter tahun."
          :action="route('backdoor.reports.pendapatan-tahunan.pdf')"
        >
          <x-backdoor.reports.select-input
            name="tahun"
            label="TAHUN LAPORAN"
            placeholder="--- Pilih Tahun ---"
            :selectedNullOption="false"
            :options="$tahunOptions"
          />
          {{-- laporan pendapatan tahunan end --}}
        </x-backdoor.reports.card>

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
          description="Jadwal sesi foto harian berdasarkan tanggal (booking_date)."
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
          description="Cek tingkat kepadatan operasional studio per harinya berdasarkan tanggal (booking_date)."
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
          description="Lihat semua ulasan pelanggan berdasarkan tanggal (created_at)."
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
          description="Daftar Klien yang terdaftar di sistem berdasarkan tanggal akun dibuat (created_at)."
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

        {{-- laporan data paket start --}}
        <x-backdoor.reports.card
          title="Laporan Data Paket"
          description="Menyajikan katalog data paket beserta variannya."
          :action="route('backdoor.reports.data-paket.pdf')"
        >
          <p class="text-sm text-stone-500">
            Laporan ini menampilkan seluruh data master katalog paket.
          </p>
        </x-backdoor.reports.card>
        {{-- laporan data paket end --}}

        {{-- laporan rekapitulasi jadwal pemotretan start --}}
        <x-backdoor.reports.card
          title="Laporan Rekapitulasi Jadwal Pemotretan"
          description="Jadwal sesi foto dengan rentang waktu Tanggal Awal s/d Tanggal Akhir berdasarkan tanggal (booking_date)."
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
