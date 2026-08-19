@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Ulasan Klien', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Nama Klien', 'Rating', 'Komentar', 'Tanggal'];
  if (auth()->user()->can('review-client-delete')) {
      $tableHeaders[] = 'Aksi';
  }
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

      {{-- stats start --}}
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
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
      {{-- stats end --}}

      {{-- table start --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-4 sm:p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama klien, rating..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container :headers="$tableHeaders">
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

              {{-- Komentar --}}
              <x-backdoor.table.cell class="max-w-50 md:max-w-xs">
                <span
                  class="block w-full cursor-help truncate text-sm text-stone-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-400"
                  tabindex="0"
                  aria-label="Komentar lengkap"
                  x-text="review.comment"
                  x-tooltip="review.comment"
                ></span>
              </x-backdoor.table.cell>

              {{-- Tanggal --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-500"
                x-text="review.created_at"
              />

              {{-- Aksi --}}
              @can('review-client-delete')
                <x-backdoor.table.actions>
                  <x-backdoor.table.action-item
                    x-on:click="deleteReview(review.id); closeDropdown()"
                    color="text-red-600"
                    text="Hapus"
                  />
                </x-backdoor.table.actions>
              @endcan

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- table end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
