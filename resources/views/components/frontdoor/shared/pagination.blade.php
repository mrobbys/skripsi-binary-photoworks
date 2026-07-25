<div class="flex justify-between items-center" x-show="state.total > 0" x-cloak>
  {{-- pagination meta start --}}
  <div class="text-sm text-stone-600">
    Menampilkan halaman <span class="font-semibold text-stone-900" x-text="state.currentPage"></span>
    dari <span class="font-semibold text-stone-900" x-text="state.lastPage"></span>
    (Total: <span class="font-semibold text-stone-900" x-text="state.total"></span> data)
  </div>
  {{-- pagination meta end --}}

  {{-- pagination controls start --}}
  <div class="flex flex-wrap items-center gap-1">
    {{-- prev btn start --}}
    <button
      class="w-10 h-10 flex items-center justify-center border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
      x-on:click="prevPage()"
      :disabled="state.currentPage <= 1 || state.isLoading">
      <i class="ri-arrow-left-s-line text-lg"></i>
    </button>
    {{-- prev btn end --}}

    {{-- page numbers start --}}
    <template x-for="(page, index) in getPages()" :key="index">
      <button
        class="w-10 h-10"
        x-on:click="goToPage(page)"
        :disabled="page === '...' || state.isLoading"
        :class="page === state.currentPage ?
            'border border-stone-500 bg-stone-500 text-stone-50 font-semibold cursor-default' :
            ((page === '...' || state.isLoading) ?
                'flex items-center justify-center text-stone-400 cursor-not-allowed' :
                'border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 transition'
            )"
        x-text="page">
      </button>
    </template>
    {{-- page numbers end --}}

    {{-- next btn start --}}
    <button
      class="w-10 h-10 flex items-center justify-center border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
      x-on:click="nextPage()"
      :disabled="state.currentPage >= state.lastPage || state.isLoading">
      <i class="ri-arrow-right-s-line text-lg"></i>
    </button>
    {{-- next btn end --}}
  </div>
  {{-- pagination controls end --}}
</div>
