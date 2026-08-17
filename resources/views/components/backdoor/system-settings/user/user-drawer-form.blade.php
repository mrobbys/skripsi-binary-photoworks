@props([
    'roles' => [],
])

{{-- drawer user start --}}
<x-shared.drawer
  openState="state.isDrawerOpen"
  closeAction="closeDrawer()"
  titleExpression="state.isEdit ? 'Edit User' : 'Tambah User'"
  ariaLabelledBy="user-drawer-title"
  formAction="submitUser()"
  maxWidth="max-w-md"
>
  {{-- form body start --}}
  <div class="space-y-6">

    {{-- input nama user start --}}
    <x-shared.input.field
      name="name"
      label="NAMA LENGKAP"
      :required="true"
    >
      <x-shared.input.text
        type="text"
        name="name"
        placeholder="Contoh: Budi Santoso"
        x-model="state.form.name"
        x-on:blur="validateField('name')"
        x-on:input="validateField('name')"
        maxlength="255"
        required
      />
    </x-shared.input.field>
    {{-- input nama user end --}}

    {{-- input email user start --}}
    <x-shared.input.field
      name="email"
      label="EMAIL"
      :required="true"
    >
      <x-shared.input.text
        type="email"
        name="email"
        placeholder="email@example.com"
        x-model="state.form.email"
        x-on:blur="validateField('email')"
        x-on:input="validateField('email')"
        maxlength="255"
        required
      />
    </x-shared.input.field>
    {{-- input email user end --}}

    {{-- input nomor hp user start --}}
    <x-shared.input.field
      name="phone"
      label="NOMOR HP"
      :required="true"
    >
      <x-shared.input.text
        type="tel"
        name="phone"
        placeholder="6281234567890"
        x-model="state.form.phone"
        x-on:blur="validateField('phone')"
        x-on:input="validateField('phone')"
        minlength="10"
        maxlength="14"
        required
      />
    </x-shared.input.field>
    {{-- input nomor hp user end --}}

    {{-- select role user start --}}
    <x-shared.input.field
      name="role"
      label="ROLE"
      :required="true"
    >
      <select
        id="userRoleSelect"
        name="role"
        x-data="userChoices({
            placeholder: true,
            placeholderValue: '--- Pilih Role ---',
            searchPlaceholderValue: 'Cari role...',
        })"
        x-modelable="value"
        x-model="state.form.role"
        x-on:change="validateField('role', $event.target.value)"
        required
        class="w-full border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-800 transition focus:border-stone-700 focus:bg-white focus:outline-none focus:ring-0"
        :class="{ 'border-red-400': state.errors.role }"
      >
        <option value="">--- Pilih Role ---</option>
        @foreach ($roles as $role)
          <option value="{{ $role->name }}">
            {{ ucfirst($role->name) }}
          </option>
        @endforeach
      </select>
    </x-shared.input.field>
    {{-- select role user end --}}

    {{-- password default info start --}}
    <template x-if="!state.isEdit">
      <div class="space-y-2">
        <label class="text-xs font-semibold uppercase tracking-wider text-stone-600">
          Password Default
        </label>
        <input
          type="text"
          value="Password123"
          readonly
          class="w-full cursor-not-allowed border border-stone-200 bg-stone-100 px-3.5 py-2.5 text-sm text-stone-500 focus:outline-none"
        />
        <p class="flex items-center gap-1 text-xs text-stone-500">
          <i
            class="ri-information-line shrink-0"
            aria-hidden="true"
          ></i>
          <span>Kata sandi default untuk akun baru, pengguna dapat mengubahnya secara mandiri di halaman profil</span>
        </p>
      </div>
    </template>
    {{-- password default info end --}}

  </div>
  {{-- form body end --}}

  {{-- drawer footer start --}}
  <x-slot:footer>
    <x-shared.button
      type="button"
      variant="secondary"
      x-on:click="closeDrawer()"
      x-bind:disabled="state.isLoading"
      value="Batal"
    />
    <x-shared.button
      type="submit"
      variant="charcoal"
      x-bind:disabled="state.isLoading || !state.isFormValid"
    >
      <span x-text="state.isLoading ? 'Menyimpan...' : (state.isEdit ? 'Simpan Perubahan' : 'Simpan User')"></span>
    </x-shared.button>
  </x-slot:footer>
  {{-- drawer footer end --}}
</x-shared.drawer>
{{-- drawer user end --}}
