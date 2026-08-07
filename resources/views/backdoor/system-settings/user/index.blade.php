@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Manajemen User', 'url' => ''],
  ];
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

      {{-- Table Card --}}
      <div class="relative overflow-visible border border-stone-200 p-4">

        <x-backdoor.table.header>
          <x-slot:left>
            <x-backdoor.table.search placeholder="Cari nama, email, nomor hp..." />
          </x-slot:left>
          <x-slot:right>
            <x-backdoor.table.add-button
              x-on:click="openDrawer()"
              text="Tambah User"
            />
          </x-slot:right>
        </x-backdoor.table.header>

        <x-backdoor.table.container headers="No,Nama,Email,Nomor HP,Role,Aksi">
          <template
            x-for="(user, index) in table.data"
            :key="user.id"
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

              {{-- Nama --}}
              <x-backdoor.table.cell
                class="font-semibold text-stone-900"
                x-text="user.name"
              />

              {{-- Email --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.email"
              />

              {{-- Nomor HP --}}
              <x-backdoor.table.cell
                class="text-sm text-stone-600"
                x-text="user.phone"
              />

              {{-- Role Badge --}}
              <x-backdoor.table.cell>
                <span
                  class="inline-block px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide"
                  :class="roleBadgeClass(user.role)"
                  x-text="user.role"
                ></span>
              </x-backdoor.table.cell>

              {{-- Aksi Dropdown --}}
              <x-backdoor.table.actions>
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

                {{-- Hapus --}}
                <x-backdoor.table.action-item
                  x-on:click="destroyUser(user.id, user.name); closeDropdown()"
                  color="text-red-600"
                  text="Hapus"
                />
              </x-backdoor.table.actions>

            </tr>
          </template>
        </x-backdoor.table.container>

        <x-backdoor.table.pagination />

      </div>

      {{-- ================================================================ --}}
      {{-- DRAWER: Form Tambah / Edit User                                   --}}
      {{-- ================================================================ --}}
      <x-shared.drawer
        openState="state.isDrawerOpen"
        closeAction="closeDrawer()"
        title="Tambah / Edit User"
        ariaLabelledBy="user-drawer-title"
        formAction="submitUser()"
      >
        {{-- ---- Body Drawer ---- --}}
        <div class="space-y-5">

          {{-- Nama Lengkap --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <p
              class="text-xs text-red-500"
              x-show="state.errors.name"
              x-text="state.errors.name"
            ></p>
            <input
              type="text"
              x-model="state.form.name"
              placeholder="Contoh: Budi Santoso"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.name }"
              required
            />
          </div>

          {{-- Email --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Email <span class="text-red-500">*</span>
            </label>
            <p
              class="text-xs text-red-500"
              x-show="state.errors.email"
              x-text="state.errors.email"
            ></p>
            <input
              type="email"
              x-model="state.form.email"
              placeholder="email@example.com"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.email }"
              required
            />
          </div>

          {{-- Nomor HP --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nomor HP <span class="text-red-500">*</span>
            </label>
            <p
              class="text-xs text-red-500"
              x-show="state.errors.phone"
              x-text="state.errors.phone"
            ></p>
            <input
              type="tel"
              x-model="state.form.phone"
              placeholder="6281234567890"
              min="10"
              max="14"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400': state.errors.phone }"
              required
            />
          </div>

          {{-- Role (Choices.js) --}}
          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Role <span class="text-red-500">*</span>
            </label>
            <p
              class="text-xs text-red-500"
              x-show="state.errors.role"
              x-text="state.errors.role"
            ></p>
            <select
              x-model="state.form.role"
              x-init="$nextTick(() => {
                  const choices = new Choices($el, {
                      searchEnabled: false,
                      itemSelectText: '',
                      shouldSort: false,
                      placeholderValue: '-- Pilih Role --'
                  });
              
                  // Sync Choices.js saat state.form.role berubah (mode edit)
                  $watch('state.form.role', (val) => {
                      if (val) {
                          choices.setChoiceByValue(val);
                      } else {
                          choices.setChoiceByValue('');
                      }
                  });
              })"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800"
              :class="{ 'border-red-400': state.errors.role }"
            >
              <option value="">-- Pilih Role --</option>
              @foreach ($roles as $role)
                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
              @endforeach
            </select>
          </div>

          {{-- Password (Read-only, hanya tampil saat Create) --}}
          <template x-if="!state.isEdit">
            <div class="space-y-1">
              <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
                Password Default
              </label>
              <input
                type="text"
                value="Password123"
                readonly
                class="w-full cursor-not-allowed border border-stone-200 bg-stone-100 px-3 py-2 text-sm text-stone-500"
              />
              <p class="text-xs text-stone-500">
                <i class="ri-information-line mr-1"></i>
                Kata sandi default untuk akun baru. Pengguna dapat mengubahnya secara mandiri di halaman profil.
              </p>
            </div>
          </template>

        </div>
        {{-- ---- End Body Drawer ---- --}}

        {{-- ---- Footer Drawer ---- --}}
        <x-slot:footer>
          <button
            type="button"
            x-on:click="closeDrawer()"
            class="border border-stone-300 px-5 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-200"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="state.isLoading"
            class="bg-stone-800 px-5 py-2 text-sm font-semibold text-white transition hover:bg-stone-900 disabled:cursor-not-allowed disabled:opacity-60"
          >
            <span
              x-show="!state.isLoading"
              x-text="state.isEdit ? 'Perbarui' : 'Simpan'"
            ></span>
            <span
              x-show="state.isLoading"
              x-cloak
            >Menyimpan...</span>
          </button>
        </x-slot:footer>
        {{-- ---- End Footer Drawer ---- --}}

      </x-shared.drawer>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
