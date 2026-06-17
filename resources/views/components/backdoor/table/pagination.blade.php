<div class="flex justify-between items-center mt-6" x-show="table.pagination.total > 0" x-cloak>
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
      x-bind:disabled="table.pagination.current_page === 1 || table.isLoading">
      <i class="ri-arrow-left-s-line text-lg"></i>
    </x-backdoor.table.pagination-button>
    {{-- prev btn end --}}

    {{-- page numbers start --}}
    <template x-for="page in table.getPages()" x-bind:key="page">
      <button
        class="w-10 h-10"
        x-on:click="table.goToPage(page)"
        x-bind:disabled="page === '...' || table.isLoading"
        x-bind:class="page === table.pagination.current_page ?
            'border border-stone-500 bg-stone-500 text-stone-50 font-semibold cursor-default' :
            ((page === '...' || table.isLoading) ?
                'flex items-center justify-center text-stone-400 cursor-not-allowed' :
                'border border-stone-300 bg-transparent text-stone-600 hover:bg-stone-100 transition'
            )"
        x-text="page">
      </button>
    </template>
    {{-- page numbers end --}}

    {{-- next btn start --}}
    <x-backdoor.table.pagination-button
      x-on:click="table.nextPage()"
      x-bind:disabled="table.pagination.current_page === table.pagination.last_page || table.isLoading">
      <i class="ri-arrow-right-s-line text-lg"></i>
    </x-backdoor.table.pagination-button>
    {{-- next btn end --}}
  </div>
  {{-- pagination controls end --}}
</div>
