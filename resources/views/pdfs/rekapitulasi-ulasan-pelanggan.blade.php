<x-layouts.pdf title="Laporan Rekapitulasi Ulasan Pelanggan">
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
    <x-pdfs.title value="LAPORAN REKAPITULASI ULASAN PELANGGAN" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data ulasan pelanggan pada periode filter ini." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[5%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[15%] border border-stone-400 p-2 text-center font-bold uppercase">Tanggal</th>
              <th class="w-[27%] border border-stone-400 p-2 font-bold uppercase">Nama Klien</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Rating</th>
              <th class="w-[53%] border border-stone-400 p-2 font-bold uppercase">Komentar</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->tanggal }}</td>
                <td class="border border-stone-300 p-2 align-top font-semibold">{{ $row->nama_klien }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-bold">{{ $row->rating }}</td>
                <td class="border border-stone-300 p-2 align-top text-stone-700">{{ $row->komentar }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    {{-- summary section start --}}
    @if ($rows->isNotEmpty())
      <div class="mb-8 flex justify-end">
        <div class="text-right text-sm">
          <p class="font-bold">Total Ulasan : {{ $totalUlasan }}</p>
          <p class="font-bold">Rata-rata Rating Periode Ini: {{ $rataRataRating }}/5.0</p>
        </div>
      </div>
    @endif
    {{-- summary section end --}}

    {{-- signature section --}}
    <x-pdfs.signature
      :printDate="$printDate"
      role="Owner"
    />
  </div>
</x-layouts.pdf>
