{{--
  COMPONENT: CHANGE PASSWORD DRAWER
--}}

<x-shared.drawer
  openState="state.isPasswordDrawerOpen"
  closeAction="closePasswordDrawer()"
  title="Ganti Password"
  maxWidth="max-w-md"
  ariaLabelledBy="change-password-drawer-title"
  formAction="submitChangePassword()"
>

  <div class="space-y-6">

    {{-- password lama start --}}
    <div>
      <label
        for="old_password"
        class="mb-2 block text-sm font-medium uppercase text-stone-700"
      >
        Password Lama <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="old_password"
        model="state.passwordForm.old_password"
        placeholder="Masukkan password lama"
      />
      <template x-if="state.passwordErrors.old_password">
        <small
          class="mt-1.5 text-red-600"
          x-text="state.passwordErrors.old_password[0]"
        ></small>
      </template>
    </div>
    {{-- password lama end --}}

    <div class="h-px bg-stone-200"></div>

    {{-- password baru --}}
    <div>
      <label
        for="password"
        class="mb-2 block text-sm font-medium uppercase text-stone-700"
      >
        Password Baru <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="password"
        model="state.passwordForm.password"
        placeholder="Min. 8 karakter, huruf kecil, huruf besar & angka"
      />
      <template x-if="state.passwordErrors.password">
        <small
          class="mt-1.5 text-red-600"
          x-text="state.passwordErrors.password[0]"
        ></small>
      </template>
    </div>

    {{-- konfirmasi password baru start --}}
    <div>
      <label
        for="password_confirmation"
        class="mb-2 block text-sm font-medium uppercase text-stone-700"
      >
        Konfirmasi Password Baru <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="password_confirmation"
        model="state.passwordForm.password_confirmation"
        placeholder="Ulangi password baru"
      />
      <template x-if="state.passwordErrors.password_confirmation">
        <small
          class="mt-1.5 text-red-600"
          x-text="state.passwordErrors.password_confirmation[0]"
        ></small>
      </template>
    </div>
    {{-- konfirmasi password baru end --}}

  </div>

  <x-slot:footer>
    <x-shared.button
      type="button"
      variant="ghost"
      value="Batal"
      x-on:click="closePasswordDrawer()"
      x-bind:disabled="state.isChangingPassword"
    />
    <x-shared.button
      type="submit"
      variant="dark"
      value="Simpan Password"
      xLoading="state.isChangingPassword"
      loadingText="Menyimpan..."
      x-bind:disabled="state.isChangingPassword || (!state.passwordForm.old_password || !state.passwordForm.password || !state.passwordForm.password_confirmation)"
    />
  </x-slot:footer>

</x-shared.drawer>
