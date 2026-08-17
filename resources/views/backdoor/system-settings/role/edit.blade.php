@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Pengaturan Sistem', 'url' => '#'],
      ['label' => 'Manajemen Role', 'url' => route('backdoor.system-settings.roles.index')],
      ['label' => 'Edit Role' , 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Edit Role — {{ $role->name }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/system-settings/role-management/Form"
>
  <x-slot:content>
    <div
      x-data="Form"
      x-init="setInitialData({ name: '{{ addslashes($role->name) }}', permissions: {{ json_encode($activePermissions) }} })"
      x-cloak
      class="w-full space-y-6"
    >

      {{-- header section start --}}
      <x-backdoor.shared.page-header-back
        :href="route('backdoor.system-settings.roles.index')"
        title="Edit Role: {{ $role->name }}"
        subtitle="Perbarui nama dan konfigurasi permission role ini."
        backLabel="Kembali Ke Manajemen Role"
      />
      {{-- header section end --}}

      {{-- form body start --}}
      <div class="space-y-6">

        {{-- section identitas role start --}}
        <div class="max-w-1/2 border border-stone-200 bg-stone-50 p-6">
          <div class="mb-4 border-b border-stone-200 pb-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-stone-700">Identitas Role</h2>
          </div>

          {{-- input nama role start --}}
          <x-shared.input.field
            name="name"
            label="NAMA ROLE"
            :required="true"
          >
            <x-shared.input.text
              type="text"
              name="name"
              placeholder="Contoh: admin studio"
              x-model="state.form.name"
              x-on:input="state.form.name = $event.target.value.toLowerCase(); validateField('name')"
              x-on:blur="validateField('name')"
              maxlength="50"
              required
            />
          </x-shared.input.field>
          {{-- input nama role end --}}
        </div>
        {{-- section identitas role end --}}

        {{-- section permission matrix start --}}
        <x-backdoor.system-settings.role.permission-matrix :grouped-permissions="$groupedPermissions" />
        {{-- section permission matrix end --}}

      </div>
      {{-- form body end --}}

      {{-- submit and cancel button start --}}
      <div class="flex items-center gap-3">
        <x-shared.button
          type="button"
          variant="charcoal"
          size="md"
          x-on:click="submit('edit', {{ $role->id }})"
          x-bind:disabled="state.isLoading || !state.isFormValid"
        >
          <span x-show="!state.isLoading">Simpan Perubahan</span>
          <span
            x-show="state.isLoading"
            x-cloak
          >Memperbarui...</span>
        </x-shared.button>
        <x-shared.button
          as="a"
          :href="route('backdoor.system-settings.roles.index')"
          variant="outline"
          size="md"
          value="Batal"
        />
      </div>
      {{-- submit and cancel button end --}}

    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
