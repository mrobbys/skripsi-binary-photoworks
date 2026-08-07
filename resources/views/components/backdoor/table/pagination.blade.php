<nav
  aria-label="Navigasi Halaman"
  class="mt-6"
  x-show="table.pagination.total > 0"
  x-cloak>
  {{-- Mobile Layout (< sm) --}}
  <div class="flex items-center justify-between sm:hidden">
    <button
      type="button"
      aria-label="Halaman sebelumnya"
      class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-stone-50 text-stone-700 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer"
      x-on:click="table.prevPage()"
      x-bind:disabled="table.pagination.current_page === 1 || table.isLoading">
      <i class="ri-arrow-left-s-line text-lg" aria-hidden="true"></i>
    </button>

    <div class="text-xs font-medium text-stone-600">
      <span class="font-bold text-stone-900" x-text="table.pagination.current_page"></span> / <span class="font-bold text-stone-900" x-text="table.pagination.last_page"></span>
    </div>

    <button
      type="button"
      aria-label="Halaman berikutnya"
      class="flex h-10 w-10 items-center justify-center border border-stone-300 bg-stone-50 text-stone-700 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer"
      x-on:click="table.nextPage()"
      x-bind:disabled="table.pagination.current_page === table.pagination.last_page || table.isLoading">
      <i class="ri-arrow-right-s-line text-lg" aria-hidden="true"></i>
    </button>
  </div>

  {{-- Desktop Layout (>= sm) --}}
  <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-4">
    {{-- pagination meta start --}}
    <div class="text-sm text-stone-600">
      Menampilkan halaman <span class="font-semibold text-stone-900" x-text="table.pagination.current_page"></span>
      dari <span class="font-semibold text-stone-900" x-text="table.pagination.last_page"></span>
      (Total: <span class="font-semibold text-stone-900" x-text="table.pagination.total"></span> data)
    </div>
    {{-- pagination meta end --}}

    {{-- pagination controls start --}}
    <div class="flex items-center gap-1">
      {{-- prev btn start --}}
      <x-backdoor.table.pagination-button
        x-on:click="table.prevPage()"
        aria-label="Halaman sebelumnya"
        x-bind:disabled="table.pagination.current_page === 1 || table.isLoading">
        <i class="ri-arrow-left-s-line text-lg" aria-hidden="true"></i>
      </x-backdoor.table.pagination-button>
      {{-- prev btn end --}}

      {{-- page numbers start --}}
      <template x-for="(page, index) in table.getPages()" x-bind:key="index">
        <button
          type="button"
          class="flex w-10 h-10 items-center justify-center text-sm cursor-pointer"
          x-on:click="table.goToPage(page)"
          x-bind:disabled="page === '...' || table.isLoading"
          x-bind:aria-current="page === table.pagination.current_page ? 'page' : false"
          x-bind:aria-label="page === '...' ? 'Lebih banyak halaman' : 'Halaman ' + page"
          x-bind:class="page === table.pagination.current_page ?
              'border border-stone-500 bg-stone-500 text-stone-50 font-semibold cursor-default' :
              ((page === '...' || table.isLoading) ?
                  'text-stone-400 cursor-not-allowed' :
                  'border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 transition'
              )"
          x-text="page">
        </button>
      </template>
      {{-- page numbers end --}}

      {{-- next btn start --}}
      <x-backdoor.table.pagination-button
        x-on:click="table.nextPage()"
        aria-label="Halaman berikutnya"
        x-bind:disabled="table.pagination.current_page === table.pagination.last_page || table.isLoading">
        <i class="ri-arrow-right-s-line text-lg" aria-hidden="true"></i>
      </x-backdoor.table.pagination-button>
      {{-- next btn end --}}
    </div>
    {{-- pagination controls end --}}
  </div>
</nav>
