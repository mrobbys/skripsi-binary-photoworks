<x-layouts.pdf title="Laporan Jadwal Operasional Harian">
  <div class="mx-auto max-w-4xl p-10">
    {{-- header section start --}}
    <div class="mb-8 flex items-start justify-between border-b-2 border-stone-900 pb-8">
      {{-- section left start --}}
      <div>
        <img
          src="{{ public_path('assets/binary-logo/binary-logo-text-black.png') }}"
          alt="Binary Photoworks"
          class="h-10 object-contain"
        >
      </div>
      {{-- section left end --}}

      {{-- section right start --}}
      <div class="text-right">
        <h1 class="text-2xl font-bold uppercase tracking-tight ">Jadwal Operasional Harian</h1>
        <div class="mt-2 text-sm font-semibold">
          <p>Tanggal: {{ $printDate }}</p>
        </div>
      </div>
      {{-- section right end --}}
    </div>
    {{-- header section end --}}

    {{-- table section start --}}
    <div class="mb-10">
      @if ($rows->isEmpty())
        <div class="py-10 text-center">
          <p class="font-medium text-stone-500">Tidak ada jadwal sesi foto untuk hari ini.</p>
        </div>
      @else
        <table class="w-full border border-stone-300 text-left text-sm">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 ">
              <th
                class="w-[5%] border-r border-stone-300 px-3 py-3 text-center text-xs font-bold uppercase tracking-wider"
              >No</th>
              <th class="w-[15%] border-r border-stone-300 px-3 py-3 text-xs font-bold uppercase tracking-wider">Waktu
                Sesi</th>
              <th class="w-[20%] border-r border-stone-300 px-3 py-3 text-xs font-bold uppercase tracking-wider">Nama
                Klien</th>
              <th class="w-[35%] border-r border-stone-300 px-3 py-3 text-xs font-bold uppercase tracking-wider">Paket &
                Background</th>
              <th class="w-[25%] px-3 py-3 text-xs font-bold uppercase tracking-wider">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-300">
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border-r border-stone-300 px-3 py-3 text-center align-top">{{ $row->no }}</td>
                <td class="border-r border-stone-300 px-3 py-3 align-top font-medium">{{ $row->time }}</td>
                <td class="border-r border-stone-300 px-3 py-3 align-top font-bold ">{{ $row->name }}
                </td>
                <td class="border-r border-stone-300 px-3 py-3 align-top">
                  <p class="font-medium ">{{ $row->package }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ $row->background }}</p>
                </td>
                <td class="px-3 py-3 align-top text-stone-700">{{ $row->notes ?: '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    {{-- footer section start --}}
    <div class="flex justify-between border-t border-stone-200 pt-8 text-xs text-stone-500">
      <div class="space-y-2">
        <p>Dicetak pada:</p>
        <p>{{ $printDate }}, pukul {{ $printTime }} WITA</p>
      </div>
      <div>
        <p>Binary Photoworks © {{ date('Y') }}</p>
      </div>
    </div>
    {{-- footer section end --}}
  </div>
</x-layouts.pdf>
