@props([
    'stats' => [],
])

{{-- stats overview start --}}
<section class="mb-8">
  <div class="mb-4">
    <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-500">
      Kartu Statistik
    </h2>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <x-backdoor.shared.stats-card
      label="Total Reservasi (Bulan Ini)"
      value="{{ $stats['totalReservasi'] }}"
      suffix="Sesi"
    />
    <x-backdoor.shared.stats-card
      label="Total Pendapatan (Bulan Ini)"
      value="{{ $stats['totalPendapatan'] }}"
    />
    <x-backdoor.shared.stats-card
      label="Total Klien Terdaftar"
      value="{{ $stats['totalKlien'] }}"
      suffix="Klien"
    />
    <x-backdoor.shared.stats-card
      label="Sesi Menunggu (Hari Ini)"
      value="{{ $stats['sesiMenunggu'] }}"
      suffix="Sesi"
    />
  </div>
</section>
{{-- stats overview end --}}
