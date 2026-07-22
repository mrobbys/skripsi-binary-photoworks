@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Ulasan Klien', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Ulasan Klien"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-reviews/Index"
>
  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      <x-backdoor.shared.page-header title="Ulasan Klien" />

      {{-- Stats Cards --}}
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">

        <x-backdoor.shared.stats-card
          label="Rata-rata Rating"
          x-text="stats.average_rating + ' / 5'"
        />

        <x-backdoor.shared.stats-card
          label="Total Ulasan"
          x-text="stats.total_reviews"
          suffix="Ulasan"
        />

        <x-backdoor.shared.stats-card
          label="Ulasan Bintang 5"
          x-text="stats.five_star_reviews"
          suffix="Ulasan"
        />

        <x-backdoor.shared.stats-card
          label="Ulasan Mengecewakan (1-2)"
          x-text="stats.disappointing_reviews"
          suffix="Ulasan"
        />

      </div>

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama klien atau nilai rating..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama Klien,Rating,Komentar,Tanggal,Aksi">
          <template
            x-for="(review, index) in table.data"
            :key="review.id"
          >
            <tr
              class="border-b border-stone-200 transition hover:bg-stone-100"
              x-show="!table.isLoading"
              x-cloak
            >

              {{-- No --}}
              <x-backdoor.table.cell
                class="text-stone-500"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />

              {{-- Nama Klien --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="review.client_name"
              />

              {{-- Rating --}}
              <x-backdoor.table.cell>
                <span
                  class="font-mono text-sm font-bold"
                  :class="{
                      'text-lime-600': review.rating >= 4,
                      'text-yellow-600': review.rating === 3,
                      'text-red-600': review.rating <= 2
                  }"
                  x-text="review.rating + ' / 5'"
                ></span>
              </x-backdoor.table.cell>

              <x-backdoor.table.cell class="max-w-xs">
                <span
                  class="block max-w-xs cursor-help truncate text-sm text-stone-700"
                  x-text="review.comment"
                  x-init="$nextTick(() => {
                      window.tippy($el, {
                          content: review.comment,
                          trigger: 'mouseenter click',
                          placement: 'top',
                          maxWidth: 320,
                      });
                  })"
                ></span>
              </x-backdoor.table.cell>

              {{-- Tanggal --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-500"
                x-text="review.created_at"
              />

              {{-- Aksi --}}
              <x-backdoor.table.actions>
                <x-backdoor.table.action-item
                  x-on:click="deleteReview(review.id); closeDropdown()"
                  color="text-red-600"
                  text="Hapus"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
