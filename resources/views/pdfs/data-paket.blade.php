<x-layouts.pdf title="Laporan Data Paket">
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
    <x-pdfs.title value="LAPORAN DATA PAKET" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data katalog paket yang tersedia." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[4%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[17%] border border-stone-400 p-2 font-bold uppercase">Kategori</th>
              <th class="w-[20%] border border-stone-400 p-2 font-bold uppercase">Nama Paket</th>
              <th class="w-[14%] border border-stone-400 p-2 font-bold uppercase">Varian Paket</th>
              <th class="w-[15%] border border-stone-400 p-2 text-right font-bold uppercase">Harga</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Durasi (Menit)</th>
              <th class="w-[12%] border border-stone-400 p-2 text-center font-bold uppercase">Booking Via</th>
              <th class="w-[8%] border border-stone-400 p-2 text-center font-bold uppercase">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-stone-300 p-2 align-top">{{ $row->kategori }}</td>
                <td class="border border-stone-300 p-2 align-top font-semibold">{{ $row->nama_paket }}</td>
                <td class="border border-stone-300 p-2 align-top">{{ $row->varian }}</td>
                <td class="border border-stone-300 p-2 text-right align-top font-semibold">{{ $row->harga }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->durasi }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->booking_via }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-semibold text-stone-700">
                  {{ $row->status }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    {{-- signature section --}}
    <x-pdfs.signature
      :printDate="$printDate"
      role="Owner"
    />
  </div>
</x-layouts.pdf>
