{{-- 
  * COMPONENT SHARED STATS CARD
  * Komponen untuk menampilkan kartu statistik / ringkasan data
  *
  * Props :
  *   * `label` : string
  *     Judul atau label statistik yang ditampilkan di bagian atas kartu.
  *   * `value` : mixed (optional)
  *     Nilai statis dari backend PHP jika tidak menggunakan Alpine.js.
  *   * `x-text` : string (optional)
  *     Ekspresi state Alpine.js untuk menampilkan nilai secara dinamis/reaktif.
  *   * `suffix` : string (optional)
  *     Teks tambahan/satuan setelah nilai (misal: "Kategori", "Foto").
--}}

@props(['label' => null, 'value' => null, 'suffix' => null])

<div
  {{ $attributes->except('x-text')->merge(['class' => 'w-full bg-stone-100 border border-stone-200 p-6 space-y-6']) }}>
  <div class="text-xs font-semibold text-stone-500 uppercase tracking-wider whitespace-nowrap">
    {{ $label }}
  </div>
  <div class="text-2xl font-heading font-bold text-stone-900 whitespace-nowrap">
    @if ($attributes->has('x-text'))
      <span x-text="{{ $attributes->get('x-text') }}"></span>
    @else
      <span>{{ $value }}</span>
    @endif

    @if ($suffix)
      {{ $suffix }}
    @endif
  </div>
</div>
