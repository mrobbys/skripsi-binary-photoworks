<x-layouts.pdf title="Laporan Rekapitulasi Jadwal Pemotretan">
  <div>
    {{-- header kop surat --}}
    <x-pdfs.kop-surat />

    {{-- metadata information --}}
    <x-pdfs.metadata>
      <x-slot:left>
        <tr>
          <td class="py-0.5 pr-2 align-top font-semibold">Cetak</td>
          <td class="py-0.5 pr-2 align-top">:</td>
          <td class="py-0.5 align-top">{{ $printedBy }}</td>
        </tr>
        <tr>
          <td class="py-0.5 pr-2 align-top font-semibold">Filter</td>
          <td class="py-0.5 pr-2 align-top">:</td>
          <td class="py-0.5 align-top">{{ $filterText }}</td>
        </tr>
      </x-slot:left>
      <x-slot:right>
        <tr>
          <td class="py-0.5 pr-2 align-top font-semibold">Tanggal Cetak</td>
          <td class="py-0.5 pr-2 align-top">:</td>
          <td class="py-0.5 align-top">{{ $printDate }}</td>
        </tr>
      </x-slot:right>
    </x-pdfs.metadata>

    {{-- title --}}
    <x-pdfs.title value="LAPORAN REKAPITULASI JADWAL PEMOTRETAN" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada jadwal pemotretan pada periode filter ini." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[4%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[12%] border border-stone-400 p-2 text-center font-bold uppercase">Tanggal</th>
              <th class="w-[15%] border border-stone-400 p-2 font-bold uppercase">Nama</th>
              <th class="w-[14%] border border-stone-400 p-2 text-center font-bold uppercase">Jam</th>
              <th class="w-[25%] border border-stone-400 p-2 font-bold uppercase">Paket & Background</th>
              <th class="w-[30%] border border-stone-400 p-2 font-bold uppercase">Keterangan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->tanggal }}</td>
                <td class="border border-stone-300 p-2 align-top font-semibold">{{ $row->nama }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-medium">{{ $row->jam }}</td>
                <td class="border border-stone-300 p-2 align-top">
                  <p class="font-medium">{{ $row->package }}</p>
                  <p class="mt-1 text-xs text-stone-500">{{ $row->background }}</p>
                </td>
                <td class="border border-stone-300 p-2 align-top text-stone-700">{{ $row->keterangan ?: '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    {{-- total summary section start --}}
    @if ($rows->isNotEmpty())
      <div class="mb-8 flex justify-end text-sm font-bold">
        <p>Total Jadwal Pemotretan Periode Ini: <span class="text-base text-stone-900">{{ $totalJadwal }} Sesi</span>
        </p>
      </div>
    @endif
    {{-- total summary section end --}}

    {{-- signature section --}}
    <x-pdfs.signature
      :printDate="$printDate"
      role="Owner"
    />
  </div>
</x-layouts.pdf>
