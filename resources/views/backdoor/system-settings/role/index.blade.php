@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
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

      {{-- title section start --}}
      <x-backdoor.shared.page-header title="Manajemen Role" />
      {{-- title section end --}}

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        {{-- table header start --}}
        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama role..." />
          </x-slot:left>
          <x-slot:right>
            <x-backdoor.table.add-button
              as="a"
              :href="route('backdoor.system-settings.roles.create')"
              text="Tambah Role"
            />
          </x-slot:right>
        </x-backdoor.table.header>
        {{-- table header end --}}

        {{-- table container start --}}
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

              {{-- no start --}}
              <x-backdoor.table.cell
                class="text-stone-500"
                x-text="index + 1 + ((table.pagination.current_page - 1) * table.pagination.per_page)"
              />
              {{-- no end --}}

              {{-- nama role start --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap font-semibold text-stone-900"
                x-text="role.name"
              />
              {{-- nama role end --}}

              {{-- jumlah permission start --}}
              <x-backdoor.table.cell
                class="whitespace-nowrap text-sm text-stone-600"
                x-text="role.permissions_count + ' Permission'"
              />
              {{-- jumlah permission end --}}

              {{-- aksi start --}}
              <td class="p-3 md:px-6 md:py-4">
                <div class="flex items-center gap-3">

                  {{-- btn detail start --}}
                  <a
                    :href="role.show_url"
                    class="text-stone-600 transition hover:text-stone-900"
                    title="Lihat Detail"
                    aria-label="Lihat Detail Role"
                  >
                    <i
                      class="ri-eye-line text-lg"
                      aria-hidden="true"
                    ></i>
                  </a>
                  {{-- btn detail end --}}

                  {{-- btn edit start --}}
                  <a
                    x-show="role.name.toLowerCase() !== 'superadmin'"
                    :href="role.edit_url"
                    class="text-yellow-600 transition hover:text-yellow-800"
                    title="Edit Role"
                    aria-label="Edit Role"
                  >
                    <i
                      class="ri-pencil-line text-lg"
                      aria-hidden="true"
                    ></i>
                  </a>
                  {{-- btn edit end --}}

                  {{-- btn hapus start --}}
                  <button
                    type="button"
                    x-show="role.name.toLowerCase() !== 'superadmin'"
                    x-on:click="deleteRole(role.id, role.name)"
                    class="text-red-600 transition hover:text-red-800"
                    title="Hapus Role"
                    aria-label="Hapus Role"
                  >
                    <i
                      class="ri-delete-bin-line text-lg"
                      aria-hidden="true"
                    ></i>
                  </button>
                  {{-- btn hapus end --}}

                </div>
              </td>
              {{-- aksi end --}}

            </tr>
          </template>
        </x-backdoor.table.container>
        {{-- table container end --}}

        {{-- pagination start --}}
        <x-backdoor.table.pagination />
        {{-- pagination end --}}

      </div>
      {{-- table card end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
