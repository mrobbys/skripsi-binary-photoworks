@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
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

      {{-- Header + Back Button --}}
      <div class="flex items-center gap-4">
        <a
          href="{{ route('backdoor.system-settings.roles.index') }}"
          class="flex h-8 w-8 items-center justify-center border border-stone-300 text-stone-500 transition hover:bg-stone-200"
        >
          <i class="ri-arrow-left-line text-base"></i>
        </a>
        <div>
          <h1 class="text-lg font-bold tracking-tight text-stone-900">{{ $role->name }}</h1>
          <p class="text-sm text-stone-500">{{ $role->permissions->count() }} izin terdaftar</p>
        </div>
      </div>

      {{-- Permission Badges per Domain Group --}}
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse ($groupedPermissions as $group => $permissions)
          <div class="border border-stone-300 bg-stone-100 p-5">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-stone-500">
              {{ ucfirst($group) }}
            </h2>
            <div class="flex flex-wrap gap-2">
              @foreach ($permissions as $permission)
                <span class="border border-stone-300 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-700">
                  {{ $permission->name }}
                </span>
              @endforeach
            </div>
          </div>
        @empty
          <div class="border border-stone-200 bg-stone-50 p-5">
            <p class="text-sm text-stone-500">Role ini belum memiliki izin apapun.</p>
          </div>
        @endforelse
      </div>

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>