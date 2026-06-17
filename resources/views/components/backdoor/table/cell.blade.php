{{-- 
  * COMPONENT TABLE CELL
  * Sel data (td) kustom untuk tabel dengan styling dasar yang konsisten.
  *
  * Props :
  *   * `value` : mixed (optional)
  *     Nilai statis dari backend PHP jika tidak menggunakan Alpine.js.
  *   * `x-text` : string (optional)
  *     Ekspresi state Alpine.js untuk menampilkan teks secara reaktif.
--}}

@props([
    'value' => null,
])

<td {{ $attributes->merge(['class' => 'px-6 py-4']) }}>
  @if (!$attributes->has('x-text'))
    {{ $value ?? $slot }}
  @endif
</td>
