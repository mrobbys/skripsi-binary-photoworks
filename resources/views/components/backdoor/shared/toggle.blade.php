{{-- 
  * COMPONENT SHARED TOGGLE
  * Switch toggle checkbox reaktif yang dikontrol menggunakan Alpine.js / standard HTML.
  *
  * Penggunaan atribut:
  *   * Semua atribut selain 'class' akan diteruskan ke elemen <input type="checkbox"> 
  *     (seperti x-model, @change, x-bind:checked, dll).
  *   * Atribut 'class' akan digabungkan pada label pembungkus terluar.
--}}

<label
  {{ $attributes->only('class')->merge(['class' => 'relative inline-flex items-center cursor-pointer select-none']) }}>
  <input
    type="checkbox"
    class="sr-only peer"
    {{ $attributes->except('class') }}>
  <div
    class="w-12 h-6 bg-stone-200 border border-stone-300  peer-focus:outline-none transition-colors peer-checked:bg-stone-700 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-stone-300 after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-6 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed">
  </div>
</label>
