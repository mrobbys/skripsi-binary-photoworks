<x-layouts.pdf title="Laporan Rekapitulasi Pendapatan Transaksi">
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
    <x-pdfs.title value="LAPORAN REKAPITULASI PENDAPATAN TRANSAKSI" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data transaksi pendapatan pada periode filter ini." />
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[4%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Tanggal Bayar</th>
              <th class="w-[17%] border border-stone-400 p-2 text-center font-bold uppercase">Kode Booking</th>
              <th class="w-[13%] border border-stone-400 p-2 font-bold uppercase">Nama Klien</th>
              <th class="w-[14%] border border-stone-400 p-2 font-bold uppercase">Paket</th>
              <th class="w-[11%] border border-stone-400 p-2 text-center font-bold uppercase">Harga Paket</th>
              <th class="w-[9%] border border-stone-400 p-2 text-center font-bold uppercase">Total Add-ons</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Jenis Pembayaran</th>
              <th class="w-[12%] border border-stone-400 p-2 text-right font-bold uppercase">Total Pendapatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->pay_date }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top font-mono font-semibold">
                  {{ $row->booking_code }}</td>
                <td class="border border-b-0 border-stone-300 p-2 align-top font-semibold">{{ $row->client_name }}</td>
                <td class="border border-b-0 border-stone-300 p-2 align-top">{{ $row->package_name }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->package_price }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top">{{ $row->addons_total }}</td>
                <td class="border border-b-0 border-stone-300 p-2 text-center align-top font-semibold uppercase">
                  {{ $row->payment_purpose }}
                </td>
                <td class="border border-b-0 border-stone-300 p-2 text-right align-top font-semibold">
                  {{ $row->total_pendapatan }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
    {{-- table section end --}}

    {{-- grand total section start --}}
    @if ($rows->isNotEmpty())
      <div class="mb-8 flex justify-end text-sm font-bold">
        <p>Total Pendapatan Periode Ini: <span class="text-base text-stone-900">{{ $grandTotal }}</span></p>
      </div>
    @endif
    {{-- grand total section end --}}

    {{-- signature section --}}
    <x-pdfs.signature
      :printDate="$printDate"
      role="Owner"
    />
  </div>
</x-layouts.pdf>
