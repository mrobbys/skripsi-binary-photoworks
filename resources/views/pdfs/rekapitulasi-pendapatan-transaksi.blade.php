<x-layouts.pdf title="Laporan Rekapitulasi Pendapatan Transaksi">
  <div>
    {{-- header kop surat --}}
    <x-pdfs.kop-surat />

    {{-- metadata information --}}
    <x-pdfs.metadata
      :printedBy="$printedBy"
      :filterText="$filterText"
      :printDate="$printDate"
    />

    {{-- title --}}
    <x-pdfs.title value="LAPORAN REKAPITULASI PENDAPATAN TRANSAKSI" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <div class="border border-stone-300 py-10 text-center">
          <p class="text-sm font-medium text-stone-500">Tidak ada data transaksi pendapatan pada periode filter ini.</p>
        </div>
      @else
        <table class="w-full border-collapse border border-stone-400 text-left text-xs">
          <thead class="table-row-group">
            <tr class="border-b border-stone-400 bg-stone-200 text-stone-900">
              <th class="w-[4%] border-r border-stone-300 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[10%] border-r border-stone-300 p-2 text-center font-bold uppercase">Tanggal Bayar</th>
              <th class="w-[17%] border-r border-stone-300 p-2 text-center fon  t-bold uppercase">Kode Booking</th>
              <th class="w-[13%] border-r border-stone-300 p-2 font-bold uppercase">Nama Klien</th>
              <th class="w-[14%] border-r border-stone-300 p-2 font-bold uppercase">Paket</th>
              <th class="w-[11%] border-r border-stone-300 p-2 text-center font-bold uppercase">Harga Paket</th>
              <th class="w-[9%] border-r border-stone-300 p-2 text-center font-bold uppercase">Total Add-ons</th>
              <th class="w-[10%] border-r border-stone-300 p-2 text-center font-bold uppercase">Jenis Pembayaran</th>
              <th class="w-[12%] p-2 text-right font-bold uppercase">Total Pendapatan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-stone-300">
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border-r border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border-r border-stone-300 p-2 text-center align-top">{{ $row->pay_date }}</td>
                <td class="border-r border-stone-300 p-2 text-center align-top font-mono font-semibold">
                  {{ $row->booking_code }}</td>
                <td class="border-r border-stone-300 p-2 align-top font-semibold">{{ $row->client_name }}</td>
                <td class="border-r border-stone-300 p-2 align-top">{{ $row->package_name }}</td>
                <td class="border-r border-stone-300 p-2 text-center align-top">{{ $row->package_price }}</td>
                <td class="border-r border-stone-300 p-2 text-center align-top">{{ $row->addons_total }}</td>
                <td class="border-r border-stone-300 p-2 text-center align-top font-semibold uppercase">
                  {{ $row->payment_purpose }}
                </td>
                <td class="p-2 text-right align-top font-semibold">
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
