{{-- 
    * TABLE CONTAINER
--}}

@props(['headers' => null])

@php
  // Konversi $headers dari String (comma-separated) menjadi sebuah Array
  if (is_string($headers)) {
      $headers = explode(',', $headers);
  }
@endphp

<div
  class="min-h-45 overflow-x-auto border border-stone-200 bg-stone-50"
  role="region"
  aria-label="Tabel Data"
>
  <table class="w-full border-collapse text-left">
    <thead class="border-b border-stone-200 bg-stone-100">
      <tr>
        @foreach ($headers as $header)
          <th
            scope="col"
            class="p-3 text-xs font-semibold uppercase tracking-wider text-stone-600 md:px-6 md:py-4"
          >{{ $header }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody class="divide-y divide-stone-200 text-sm">
      {{-- state loading start --}}
      <tr
        x-show="table.isLoading"
        x-cloak
      >
        <td
          colspan="{{ count($headers) }}"
          class="px-6 py-14 text-center text-stone-500"
        >
          <i
            class="ri-loader-2-line mr-2 inline-block animate-spin align-middle text-xl"
            aria-hidden="true"
          ></i>
          <span class="align-middle">Memuat data...</span>
        </td>
      </tr>
      {{-- state loading end --}}

      {{-- empty state start --}}
      <tr
        x-show="!table.isLoading && table.data.length === 0"
        x-cloak
      >
        <td
          colspan="{{ count($headers) }}"
          class="px-6 py-12 text-center text-stone-500"
        >
          Tidak ada data ditemukan.
        </td>
      </tr>
      {{-- empty state end --}}

      {{ $slot }}
    </tbody>
  </table>
</div>
