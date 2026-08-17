@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
    ['label' => 'Pengaturan Sistem', 'url' => '#'],
    ['label' => 'Manajemen Role', 'url' => route('backdoor.system-settings.roles.index')],
    ['label' => $role->name, 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Role — {{ $role->name }}"
  :breadcrumbs="$breadcrumbs"
>
  <x-slot:content>
    <div class="w-full space-y-6">

      {{-- header section start --}}
      <x-backdoor.shared.page-header-back
        :href="route('backdoor.system-settings.roles.index')"
        title="{{ $role->name }}"
        subtitle="{{ $role->permissions->count() }} izin terdaftar"
        backLabel="Kembali Ke Manajemen Role"
      />
      {{-- header section end --}}

      {{-- permission matrix start --}}
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse ($groupedPermissions as $group => $permissions)
          {{-- card group start --}}
          <div class="border border-stone-300 bg-stone-100 p-5">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="text-xs font-semibold uppercase tracking-wider text-stone-600">
                {{ ucfirst($group) }}
              </h2>
              <span class="text-xs font-medium text-stone-400">
                {{ $permissions->count() }} izin
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              @foreach ($permissions as $permission)
                <span class="border border-stone-300 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-700">
                  {{ $permission->name }}
                </span>
              @endforeach
            </div>
          </div>
          {{-- card group end --}}
        @empty
          {{-- empty state start --}}
          <div class="col-span-full border border-stone-200 bg-stone-50 p-6 text-center">
            <p class="text-sm text-stone-500">Role ini belum memiliki izin apapun</p>
          </div>
          {{-- empty state end --}}
        @endforelse
      </div>
      {{-- permission matrix end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>