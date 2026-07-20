@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Manajemen Role', 'url' => route('backdoor.system-settings.roles.index')],
      ['label' => 'Tambah Role', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Tambah Role"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/Form"
>
  <x-slot:content>
    <div
      x-data="Form"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- Header + Back --}}
      <div class="flex items-center gap-4">
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="flex h-8 w-8 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200"
        >
          <i class="ri-arrow-left-line text-base"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-stone-900">Tambah Role Baru</h1>
          <p class="text-sm text-stone-500">Buat role dengan kumpulan permission yang dikonfigurasi.</p>
        </div>
      </div>

      {{-- Form Body --}}
      <div class="space-y-6">

        {{-- Panel Kiri: Identitas Role --}}
        <div class="max-w-1/2 border border-stone-200 bg-stone-50 p-6">
          <div class="mb-4 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Identitas Role</h2>
          </div>

          <div class="space-y-1">
            <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
              Nama Role
            </label>
            {{-- Error name: di bawah label, di atas input --}}
            <p
              class="text-xs text-red-500"
              x-show="errors.name"
              x-text="errors.name"
            ></p>
            <input
              type="text"
              x-model="name"
              placeholder="Contoh: Admin Studio"
              class="w-full border border-stone-300 bg-white px-3 py-2 text-sm text-stone-800 outline-none placeholder:text-stone-400 focus:border-stone-500"
              :class="{ 'border-red-400 focus:border-red-400': errors.name }"
            />
          </div>
        </div>

        {{-- Panel Kanan: Matriks Permission --}}
        <div class="border border-stone-200 bg-stone-50 p-6">
          <div class="mb-1 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Isi Permission</h2>
          </div>
          {{-- Error permissions: di bawah heading "Isi Permission", di atas grid card --}}
          <p
            class="mb-4 text-xs text-red-500"
            x-show="errors.permissions"
            x-text="errors.permissions"
          ></p>

          <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($groupedPermissions as $group => $permissions)
              @php $permNames = $permissions->pluck('name')->toArray(); @endphp
              <div class="border border-stone-300 bg-stone-100 p-4">
                <div class="mb-3 flex items-center justify-between">
                  <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-500">
                    {{ ucfirst($group) }}
                  </h3>
                  <div class="flex gap-3">
                    <button
                      type="button"
                      x-on:click="selectAll({{ json_encode($permNames) }})"
                      class="text-xs text-stone-500 underline transition hover:text-stone-800"
                    >Pilih Semua</button>
                    <button
                      type="button"
                      x-on:click="deselectAll({{ json_encode($permNames) }})"
                      class="text-xs text-stone-400 underline transition hover:text-stone-700"
                    >Hapus Pilihan</button>
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                  @foreach ($permissions as $permission)
                    <label
                      class="flex cursor-pointer items-center gap-2 border border-stone-300 bg-stone-50 px-3 py-2 text-sm text-stone-700 transition hover:bg-stone-200"
                    >
                      <input
                        type="checkbox"
                        :checked="isChecked('{{ $permission->name }}')"
                        x-on:change="togglePermission('{{ $permission->name }}')"
                        class="h-4 w-4 border-stone-300 accent-stone-800"
                      />
                      <span class="">{{ $permission->name }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>

      </div>

      {{-- Submit & Cancel --}}
      <div class="flex items-center gap-3">
        <button
          type="button"
          x-on:click="submit('create')"
          :disabled="isLoading"
          class="bg-stone-800 px-6 py-2 text-sm font-semibold text-white transition hover:bg-stone-900 disabled:cursor-not-allowed disabled:opacity-60"
        >
          <span x-show="!isLoading">Simpan Role</span>
          <span
            x-show="isLoading"
            x-cloak
          >Menyimpan...</span>
        </button>
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="border border-stone-300 px-6 py-2 text-sm font-semibold text-stone-600 transition hover:bg-stone-100"
        >
          Batal
        </a>
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
