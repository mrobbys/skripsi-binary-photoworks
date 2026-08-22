<section class="w-full">
  {{-- header start --}}
  <div
    data-animate="faq-header"
    class="mb-18 text-center"
  >
    <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
      Hal yang Sering Ditanyakan
    </h2>
  </div>
  {{-- header end --}}

  {{-- accordion list start --}}
  <div
    id="faq-list"
    class="mx-auto max-w-3xl border-t border-stone-200"
  >
    <template
      x-for="(item, i) in state.faqs"
      :key="i"
    >
      <div
        data-animate="faq-item"
        class="border-b border-stone-200 py-2"
        x-cloak
      >
        {{-- btn start --}}
        <button
          type="button"
          tabindex="-1"
          :id="'faq-header-' + i"
          :aria-controls="'faq-answer-' + i"
          x-bind:aria-expanded="state.openIndex === i ? 'true' : 'false'"
          class="group flex w-full select-none items-center justify-between gap-4 px-2 py-4 text-left transition-colors duration-300 hover:bg-stone-100 focus:outline-none"
          x-on:click="toggle(i); $el.blur()"
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
        {{-- btn end --}}

        {{-- answer start --}}
        <div
          x-cloak
          x-show="state.openIndex === i"
          x-collapse.duration.300ms
          :id="'faq-answer-' + i"
          :aria-labelledby="'faq-header-' + i"
          role="region"
        >
          <div class="bg-stone-100 p-4">
            <p
              class="text-sm leading-relaxed text-stone-700 sm:text-base"
              x-text="item.a"
            ></p>
          </div>
        </div>
        {{-- answer end --}}
      </div>
    </template>
  </div>
  {{-- accordion list end --}}

  {{-- link start --}}
  <div
    data-animate="faq-footer"
    class="mt-10 text-center"
  >
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.faq') }}"
      variant="outline"
      size="md"
      value="Lihat Semua Pertanyaan"
      class="hover:scale-105"
    />
  </div>
  {{-- link end --}}
</section>
