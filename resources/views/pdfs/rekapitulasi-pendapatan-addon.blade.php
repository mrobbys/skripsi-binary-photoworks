<x-layouts.pdf title="Laporan Rekapitulasi Pendapatan Add-ons">
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
    <x-pdfs.title value="LAPORAN REKAPITULASI PENDAPATAN ADD-ONS" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data pendapatan add-on pada periode filter ini." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[6%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[52%] border border-stone-400 p-2 font-bold uppercase">Nama Add-on</th>
              <th class="w-[18%] border border-stone-400 p-2 text-center font-bold uppercase">Total Terjual</th>
              <th class="w-[24%] border border-stone-400 p-2 text-right font-bold uppercase">Total Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-b-0 border-stone-300 p-2 align-top font-semibold">{{ $row->name }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->total_qty }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-right align-top">{{ $row->total_pendapatan }}
                </td>
              </tr>
            @endforeach
          </tbody>
          {{-- footer total row --}}
          <tfoot>
            <tr class="bg-stone-200 font-bold">
              <td
                class="border border-stone-400 p-2 text-left"
                colspan="2"
              >Total</td>
              <td class="border border-stone-400 p-2 text-center">{{ $grandTotalQty }}</td>
              <td class="border border-stone-400 p-2 text-right">{{ $grandTotalPendapatan }}</td>
            </tr>
          </tfoot>
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
