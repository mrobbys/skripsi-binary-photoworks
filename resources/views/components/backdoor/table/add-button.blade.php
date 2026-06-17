@props(["text" => "Tambah"])

<button
  {{ $attributes->merge([
      "type" => "button",
      "class" =>
          "bg-stone-500 text-stone-50 px-4 py-2 border border-stone-500 hover:bg-stone-600 transition font-semibold text-sm",
  ]) }}>
  + {{ $text }}
</button>
