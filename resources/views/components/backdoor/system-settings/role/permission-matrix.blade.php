@props([
    'groupedPermissions' => [],
])

{{-- permission matrix card start --}}
<div class="border border-stone-200 bg-stone-50 p-6">
  <div class="mb-1 pb-3">
    <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Isi Permission</h2>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    @foreach ($groupedPermissions as $group => $permissions)
      @php $permNames = $permissions->pluck('name')->toArray(); @endphp
      {{-- card group start --}}
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
            <label class="flex cursor-pointer items-center gap-2 border border-stone-300 bg-stone-50 px-3 py-2 text-sm text-stone-700 transition hover:bg-stone-200">
              <input
                type="checkbox"
                :checked="isChecked('{{ $permission->name }}')"
                x-on:change="togglePermission('{{ $permission->name }}')"
                class="h-4 w-4 border-stone-300 accent-stone-800"
              />
              <span>{{ $permission->name }}</span>
            </label>
          @endforeach
        </div>
      </div>
      {{-- card group end --}}
    @endforeach
  </div>
</div>
{{-- permission matrix card end --}}
