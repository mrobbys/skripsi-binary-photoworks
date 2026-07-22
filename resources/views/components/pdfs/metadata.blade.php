@props(['printedBy', 'filterText' => null, 'printDate'])

<div class="mb-6 flex justify-between text-stone-800">
  <div class="space-y-1">
    @if ($printedBy)
      <p><span class="inline-block w-16 font-semibold">Cetak</span> : {{ $printedBy }}</p>
    @endif
    @if ($filterText)
      <p><span class="inline-block w-16 font-semibold">Filter</span> : {{ $filterText }}</p>
    @endif
  </div>
  <div class="text-right">
    @if ($printDate)
      <p><span class="font-semibold">Tanggal Cetak</span> : {{ $printDate }}</p>
    @endif
  </div>
</div>
