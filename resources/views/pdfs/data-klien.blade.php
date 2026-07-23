<x-layouts.pdf title="Laporan Data Klien">
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
    <x-pdfs.title value="LAPORAN DATA KLIEN" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data klien yang mendaftar pada periode filter ini." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[4%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[27%] border border-stone-400 p-2 font-bold uppercase">Nama Klien</th>
              <th class="w-[18%] border border-stone-400 p-2 text-center font-bold uppercase">No WhatsApp</th>
              <th class="w-[28%] border border-stone-400 p-2 font-bold uppercase">Email</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Total Booking</th>
              <th class="w-[13%] border border-stone-400 p-2 text-center font-bold uppercase">Tanggal Bergabung</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-stone-300 p-2 align-top font-semibold">{{ $row->nama_klien }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->whatsapp }}</td>
                <td class="border border-stone-300 p-2 align-top">{{ $row->email }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-semibold">{{ $row->total_booking }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->tanggal_bergabung }}</td>
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
        <p>Total Klien Periode Ini: <span class="text-base text-stone-900">{{ $totalKlien }} Klien</span></p>
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
