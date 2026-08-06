<x-layouts.frontdoor.index
  title="FAQ"
  jsModule="frontdoor/faq/Faq"
>
  <x-slot:content>
    <section
      x-cloak
      x-data="Faq"
      class="mx-auto max-w-3xl"
    >

      {{-- header start --}}
      <div class="mb-10">
        <h1 class="font-serif text-3xl font-semibold text-stone-900 sm:text-4xl">
          Pertanyaan Yang Sering Ditanyakan
        </h1>
        <p class="mt-3 w-full text-stone-500 md:w-3/4">
          Temukan jawaban cepat seputar layanan, sistem reservasi, pembayaran,
          dan hasil foto studio kami.
        </p>
      </div>
      {{-- header end --}}

      {{-- btn kategori start --}}
      <div class="mb-8 flex flex-wrap gap-2">
        <x-shared.button
          value="Umum"
          size="md"
          variant="custom"
          x-on:click="setCategory('umum')"
          x-bind:class="state.activeCategory === 'umum' ?
              'bg-stone-900 text-stone-50 border border-transparent' :
              'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
        />
        <x-shared.button
          value="Pemesanan"
          size="md"
          variant="custom"
          x-on:click="setCategory('pemesanan')"
          x-bind:class="state.activeCategory === 'pemesanan' ?
              'bg-stone-900 text-stone-50 border border-transparent' :
              'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
        />
        <x-shared.button
          value="Pembayaran"
          size="md"
          variant="custom"
          x-on:click="setCategory('pembayaran')"
          x-bind:class="state.activeCategory === 'pembayaran' ?
              'bg-stone-900 text-stone-50 border border-transparent' :
              'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
        />
        <x-shared.button
          value="Hasil Foto"
          size="md"
          variant="custom"
          x-on:click="setCategory('hasil-foto')"
          x-bind:class="state.activeCategory === 'hasil-foto' ?
              'bg-stone-900 text-stone-50 border border-transparent' :
              'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
        />
      </div>
      {{-- btn kategori end --}}

      {{-- accordion list start --}}
      <div class="border-t border-stone-200">
        <template
          x-for="(item, i) in state.activeFaqs"
          :key="state.activeCategory + '-' + i"
        >
          <div
            class="border-b border-stone-200 py-2"
            x-cloak
          >
            <button
              type="button"
              :id="'faq-header-' + i"
              :aria-controls="'faq-answer-' + i"
              x-bind:aria-expanded="state.openIndex === i ? 'true' : 'false'"
              class="group flex w-full items-center justify-between gap-4 px-2 py-4 text-left transition-colors duration-300 hover:bg-stone-100 focus:outline-none"
              x-on:click="toggle(i)"
            >
              <span
                class="text-sm text-stone-900 sm:text-base"
                x-bind:class="state.openIndex === i ? 'font-semibold' : 'font-medium'"
                x-text="item.q"
              ></span>
              <i
                class="ri-arrow-down-s-line shrink-0 text-xl text-stone-400 transition-transform duration-200"
                x-bind:class="state.openIndex === i ? 'rotate-180 text-stone-700' : ''"
                aria-hidden="true"
              ></i>
            </button>
            <div
              x-cloak
              x-show="state.openIndex === i"
              x-collapse
              :id="'faq-answer-' + i"
              :aria-labelledby="'faq-header-' + i"
              role="region"
              class="bg-stone-100 p-2"
            >
              <p
                class="pb-5 text-sm text-stone-700 sm:text-base"
                x-text="item.a"
              ></p>
            </div>
          </div>
        </template>
      </div>
      {{-- accordion list end --}}

      {{-- footer whatsapp start --}}
      <div class="mt-16 border-t border-stone-200 pt-6">
        <p class="text-sm text-stone-500">
          Pertanyaan Anda belum terjawab? Hubungi kami melalui
          <a
            href="https://wa.me/{{ config('studio.whatsapp') }}"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="WhatsApp (buka di tab baru)"
            class="font-bold text-stone-800 underline underline-offset-2 hover:text-stone-900"
          >WhatsApp</a>
        </p>
      </div>
      {{-- footer whatsapp end --}}

    </section>
  </x-slot:content>
</x-layouts.frontdoor.index>
