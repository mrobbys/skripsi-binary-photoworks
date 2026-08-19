@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Manajemen User', 'url' => ''],
  ];

  $tableHeaders = ['No', 'Nama', 'Email', 'Nomor HP', 'Role'];
  if (auth()->user()->canany(['user-management-update', 'user-management-delete'])) {
      $tableHeaders[] = 'Aksi';
  }
@endphp

<x-layouts.backdoor.index
  title="Manajemen User"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/user-management/Index"
>
  <x-slot:content>
    <div
      x-data="Index"
      x-cloak
      class="w-full space-y-6"
    >

      <x-backdoor.shared.page-header title="Manajemen User" />

      {{-- table card start --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama, email, no. HP..." />
          </x-slot:left>
          <x-slot:right>
            @can('user-management-create')
              <x-backdoor.table.add-button
                x-on:click="openDrawer()"
                text="Tambah User"
              />
            @endcan
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container :headers="$tableHeaders">
          <template
            x-for="(user, index) in table.data"
            :key="user.id"
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

              {{-- nama start --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="user.name"
              />
              {{-- nama end --}}

              {{-- email start --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.email"
              />
              {{-- email end --}}

              {{-- nomor hp start --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.phone"
              />
              {{-- nomor hp end --}}

              {{-- role start --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="roleBadgeClass(user.role)"
                  x-text="user.role"
                ></span>
              </x-backdoor.table.cell>
              {{-- role end --}}

              {{-- aksi dropdown start --}}
              @canany(['user-management-update', 'user-management-delete'])
                <x-backdoor.table.actions>
                  @can('user-management-update')
                    {{-- Edit --}}
                    <x-backdoor.table.action-item
                      x-on:click="editUser(user); closeDropdown()"
                      color="text-yellow-600"
                      text="Edit"
                    />

                    {{-- Reset Password --}}
                    <x-backdoor.table.action-item
                      x-on:click="resetPassword(user.id, user.name); closeDropdown()"
                      color="text-sky-600"
                      text="Reset Password"
                    />
                  @endcan

                  @can('user-management-delete')
                    {{-- Hapus --}}
                    <x-backdoor.table.action-item
                      x-on:click="destroyUser(user.id, user.name); closeDropdown()"
                      color="text-red-600"
                      text="Hapus"
                    />
                  @endcan
                </x-backdoor.table.actions>
              @endcanany
              {{-- aksi dropdown end --}}

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>
      {{-- table card end --}}

      {{-- drawer user start --}}
      @canany(['user-management-create', 'user-management-update'])
        <x-backdoor.system-settings.user.user-drawer-form :roles="$roles" />
      @endcanany
      {{-- drawer user end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
