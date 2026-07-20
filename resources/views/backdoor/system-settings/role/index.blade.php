@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Manajemen Role', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Manajemen Role"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/Index"
>
  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      <x-backdoor.shared.page-header title="Manajemen Role" />

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 bg-stone-50 p-6">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama role..." />
          </x-slot:left>
          <x-slot:right>
            <a
              href="{{ route('backdoor.system-settings.roles.create') }}"
              class="inline-flex items-center gap-2 bg-stone-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-900"
            >
              <i class="ri-add-line text-base"></i>
              Tambah Role
            </a>
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama Role,Jumlah Permission,Aksi">
          <template
            x-for="(role, index) in table.data"
            :key="role.id"
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

              {{-- Nama Role --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="role.name"
              />

              {{-- Jumlah Permission --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="role.permissions_count + ' Permission'"
              />

              {{-- Aksi: 3 tombol ikon inline (BUKAN dropdown) --}}
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">

                  {{-- Detail --}}
                  <a
                    :href="role.show_url"
                    class="text-stone-600 transition hover:text-stone-900"
                    title="Lihat Detail"
                  >
                    <i class="ri-eye-line text-lg"></i>
                  </a>

                  {{-- Edit --}}
                  <a
                    :href="role.edit_url"
                    class="text-yellow-600 transition hover:text-yellow-800"
                    title="Edit Role"
                  >
                    <i class="ri-pencil-line text-lg"></i>
                  </a>

                  {{-- Hapus --}}
                  <button
                    type="button"
                    x-on:click="deleteRole(role.id, role.name)"
                    class="text-red-600 transition hover:text-red-800"
                    title="Hapus Role"
                  >
                    <i class="ri-delete-bin-line text-lg"></i>
                  </button>

                </div>
              </td>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
