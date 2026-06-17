{{-- 
  * COMPONENT TABLE PAGINATION BUTTON
  * Tombol navigasi halaman dalam tabel
--}}

<button
  {{ $attributes->merge([
      "type" => "button",
      "class" =>
          "w-10 h-10 flex items-center justify-center border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 disabled:opacity-40 disabled:cursor-not-allowed transition",
  ]) }}>
  {{ $slot }}
</button>
