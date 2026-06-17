{{-- 
  * COMPONENT TABLE ACTION ITEM
  * Tombol aksi di dalam dropdown menu aksi (context menu) tabel.
  *
  * Props :
  *   * `color` : string (optional, default: "text-stone-700")
  *     Class warna teks Tailwind (misal: "text-red-600").
--}}

@props([
    "color" => "text-stone-700",
    "text" => "",
])

<button
  {{ $attributes->merge([
      "type" => "button",
      "class" => "block w-full text-left px-4 py-2 text-sm {$color} hover:bg-stone-200 transition font-medium cursor-pointer",
  ]) }}>
  {{ $text }}
</button>
