@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Activity Logs', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Activity Logs"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/activity-logs/ActivityLogs"
>
  <x-slot:content>
    <div
      x-data="ActivityLogs"
      x-cloak
      class="w-full space-y-6"
    >

      <x-backdoor.shared.page-header title="Activity Logs" />

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari aktivitas, user..." />
          </x-slot:left>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Waktu Sesi,Pelaku,Modul & ID,Aktivitas,Aksi">
          <template
            x-for="(log, index) in table.data"
            :key="log.id"
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

              {{-- Waktu Sesi --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap font-mono text-sm text-stone-600"
                x-text="log.waktu_sesi"
              />

              {{-- Pelaku --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="log.pelaku"
              />

              {{-- Modul & ID --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="log.modul"
              />

              {{-- Aktivitas (Badge) --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="badgeClass(log.aktivitas)"
                  x-text="log.aktivitas"
                ></span>
              </x-backdoor.table.cell>

              {{-- Aksi: Tombol Lihat Detail --}}
              <x-backdoor.table.cell>
                <x-shared.button
                  x-on:click="openDetail(log.properties)"
                  size="sm"
                  class="border border-stone-300 text-stone-700 hover:bg-stone-200"
                  value="Lihat Detail"
                />
              </x-backdoor.table.cell>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

      {{-- Modal Detail Perubahan --}}
      <div
        x-show="modal.isOpen"
        x-transition
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        x-on:click.self="closeDetail()"
      >
        <div class="w-full max-w-2xl border border-stone-300 bg-white">

          {{-- Header Modal --}}
          <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">
              Detail Perubahan
            </h2>
            <button
              type="button"
              x-on:click="closeDetail()"
              class="text-stone-400 transition hover:text-stone-700"
              aria-label="Tutup modal"
            >
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>

          {{-- Body Modal: JSON Pre --}}
          <div class="p-6">
            <pre
              class="max-h-96 overflow-auto border border-stone-200 bg-stone-100 p-4 font-mono text-xs leading-relaxed text-stone-700"
              x-text="formatProperties(modal.properties)"
            ></pre>
          </div>

          {{-- Footer Modal --}}
          <div class="flex justify-end border-t border-stone-200 px-6 py-3">
            <x-shared.button
              x-on:click="closeDetail()"
              size="md"
              class="bg-stone-800 text-xs font-semibold uppercase tracking-wider text-white hover:bg-stone-900"
              value="Tutup"
            />
          </div>

        </div>
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
