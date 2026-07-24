<x-layouts.pdf title="Laporan Rekapitulasi Pemesanan">
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
        <tr>
          <td class="py-0.5 pr-2 align-top font-semibold">Status</td>
          <td class="py-0.5 pr-2 align-top">:</td>
          <td class="py-0.5 align-top">{{ $statusText }}</td>
        </tr>
      </x-slot:right>
    </x-pdfs.metadata>

    {{-- title --}}
    <x-pdfs.title value="LAPORAN REKAPITULASI PEMESANAN" />

    {{-- table section start --}}
    <div class="mb-8">
      @if ($rows->isEmpty())
        <x-pdfs.empty-state message="Tidak ada data rekapitulasi pemesanan pada periode filter ini." />
      @else
        <table class="w-full border-collapse text-left text-xs">
          <thead class="table-row-group">
            <tr class="bg-stone-200 text-stone-900">
              <th class="w-[4%] border border-stone-400 p-2 text-center font-bold uppercase">No</th>
              <th class="w-[18%] border border-stone-400 p-2 text-center font-bold uppercase">Kode Booking</th>
              <th class="w-[18%] border border-stone-400 p-2 font-bold uppercase">Nama Klien</th>
              <th class="w-[14%] border border-stone-400 p-2 text-center font-bold uppercase">No WhatsApp</th>
              <th class="w-[24%] border border-stone-400 p-2 font-bold uppercase">Layanan Utama (Paket)</th>
              <th class="w-[10%] border border-stone-400 p-2 text-center font-bold uppercase">Jadwal Sesi</th>
              <th class="w-[12%] border border-stone-400 p-2 text-center font-bold uppercase">Status Booking</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($rows as $row)
              <tr class="even:bg-stone-50">
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->no }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-mono font-semibold">
                  {{ $row->booking_code }}</td>
                <td class="border border-stone-300 p-2 align-top font-semibold">{{ $row->client_name }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->phone_number }}</td>
                <td class="border border-stone-300 p-2 align-top">{{ $row->package_name }}</td>
                <td class="border border-stone-300 p-2 text-center align-top">{{ $row->session_schedule }}</td>
                <td class="border border-stone-300 p-2 text-center align-top font-semibold">
                  {{ $row->status_label }}
                </td>
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
        <p>Total Pemesanan Periode Ini: <span class="text-base text-stone-900">{{ $totalReservasi }} Sesi</span></p>
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
