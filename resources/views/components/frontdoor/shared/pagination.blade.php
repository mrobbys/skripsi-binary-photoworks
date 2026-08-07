<nav
  aria-label="Navigasi Halaman"
  class="mt-6"
  x-show="state.total > 0"
  x-cloak
>
  {{-- Mobile Layout (< sm) --}}
  <div class="flex items-center justify-between sm:hidden">
    <button
      type="button"
      aria-label="Halaman sebelumnya"
      class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-stone-50 text-stone-700 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
      x-on:click="prevPage()"
      :disabled="state.currentPage <= 1 || state.isLoading"
    >
      <i class="ri-arrow-left-s-line text-lg" aria-hidden="true"></i>
    </button>

    <div class="text-xs font-medium text-stone-600">
      <span class="font-bold text-stone-900" x-text="state.currentPage"></span> / <span class="font-bold text-stone-900" x-text="state.lastPage"></span>
    </div>

    <button
      type="button"
      aria-label="Halaman berikutnya"
      class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-stone-50 text-stone-700 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
      x-on:click="nextPage()"
      :disabled="state.currentPage >= state.lastPage || state.isLoading"
    >
      <i class="ri-arrow-right-s-line text-lg" aria-hidden="true"></i>
    </button>
  </div>

  {{-- Desktop Layout (>= sm) --}}
  <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-4">
    {{-- pagination meta start --}}
    <div class="text-sm text-stone-600">
      Menampilkan halaman <span class="font-semibold text-stone-900" x-text="state.currentPage"></span>
      dari <span class="font-semibold text-stone-900" x-text="state.lastPage"></span>
      (Total: <span class="font-semibold text-stone-900" x-text="state.total"></span> data)
    </div>
    {{-- pagination meta end --}}

    {{-- pagination controls start --}}
    <div class="flex items-center gap-1">
      {{-- prev btn start --}}
      <button
        type="button"
        aria-label="Halaman sebelumnya"
        class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-transparent text-stone-600 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
        x-on:click="prevPage()"
        :disabled="state.currentPage <= 1 || state.isLoading"
      >
        <i class="ri-arrow-left-s-line text-lg" aria-hidden="true"></i>
      </button>
      {{-- prev btn end --}}

      {{-- page numbers start --}}
      <template x-for="(page, index) in getPages()" :key="index">
        <button
          type="button"
          class="flex h-10 w-10 items-center justify-center text-sm"
          x-on:click="goToPage(page)"
          :disabled="page === '...' || state.isLoading"
          :aria-current="page === state.currentPage ? 'page' : false"
          :aria-label="page === '...' ? 'Lebih banyak halaman' : 'Halaman ' + page"
          :class="page === state.currentPage ?
              'border border-stone-500 bg-stone-500 text-stone-50 font-semibold cursor-default' :
              ((page === '...' || state.isLoading) ?
                  'text-stone-400 cursor-not-allowed' :
                  'border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 transition'
              )"
          x-text="page"
        ></button>
      </template>
      {{-- page numbers end --}}

      {{-- next btn start --}}
      <button
        type="button"
        aria-label="Halaman berikutnya"
        class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-transparent text-stone-600 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40"
        x-on:click="nextPage()"
        :disabled="state.currentPage >= state.lastPage || state.isLoading"
      >
        <i class="ri-arrow-right-s-line text-lg" aria-hidden="true"></i>
      </button>
      {{-- next btn end --}}
    </div>
    {{-- pagination controls end --}}
  </div>
</nav>
