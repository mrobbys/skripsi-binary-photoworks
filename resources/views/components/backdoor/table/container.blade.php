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

<div class="overflow-x-auto bg-stone-50 border border-stone-200 min-h-[180px]">
  <table class="w-full text-left border-collapse">
    <thead class="bg-stone-100 border-b border-stone-200">
      <tr>
        @foreach ($headers as $header)
          <th class="px-6 py-3.5 text-xs font-semibold text-stone-600 uppercase tracking-wider">{{ $header }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody class="divide-y divide-stone-200 text-sm" x-init="autoAnimate($el)">
      {{-- state loading start --}}
      <tr x-show="table.isLoading" x-cloak>
        <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-stone-500">
          <i class="ri-loader-2-line animate-spin inline-block text-xl mr-2 align-middle"></i>
          <span class="align-middle">Memuat data...</span>
        </td>
      </tr>
      {{-- state loading end --}}

      {{-- empty state start --}}
      <tr x-show="!table.isLoading && table.data.length === 0" x-cloak>
        <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center text-stone-500">
          Tidak ada data ditemukan.
        </td>
      </tr>
      {{-- empty state end --}}

      {{ $slot }}
    </tbody>
  </table>
</div>
