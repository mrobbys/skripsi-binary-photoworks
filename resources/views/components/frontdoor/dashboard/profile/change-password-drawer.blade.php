{{--
  COMPONENT: CHANGE PASSWORD DRAWER
--}}

<x-shared.drawer
  openState="state.isPasswordDrawerOpen"
  closeAction="closePasswordDrawer()"
  title="Ganti Password"
  maxWidth="max-w-md"
  ariaLabelledBy="change-password-drawer-title"
  formAction="submitChangePassword()">

  <div class="space-y-6">

    {{-- password lama start --}}
    <div>
      <label for="old_password" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Password Lama <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="old_password"
        model="state.passwordForm.old_password"
        placeholder="Masukkan password lama" />
      <template x-if="state.passwordErrors.old_password">
        <small class="mt-1.5 text-red-600" x-text="state.passwordErrors.old_password[0]"></small>
      </template>
    </div>
    {{-- password lama end --}}

    <div class="h-px bg-stone-200"></div>

    {{-- password baru --}}
    <div>
      <label for="password" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Password Baru <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="password"
        model="state.passwordForm.password"
        placeholder="Min. 8 karakter, huruf kecil, huruf besar & angka" />
      <template x-if="state.passwordErrors.password">
        <small class="mt-1.5 text-red-600" x-text="state.passwordErrors.password[0]"></small>
      </template>
    </div>

    {{-- konfirmasi password baru start --}}
    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-stone-700 uppercase mb-2">
        Konfirmasi Password Baru <span class="text-red-500">*</span>
      </label>
      <x-shared.input.password
        id="password_confirmation"
        model="state.passwordForm.password_confirmation"
        placeholder="Ulangi password baru" />
    </div>
    {{-- konfirmasi password baru end --}}

  </div>

  <x-slot:footer>
    <x-shared.button
      type="button"
      x-on:click="closePasswordDrawer()"
      x-bind:disabled="state.isChangingPassword"
      class="text-stone-600 hover:text-stone-900 transition font-semibold text-sm cursor-pointer disabled:opacity-50"
      value="Batal" />
    <x-shared.button
      type="submit"
      x-bind:disabled="state.isChangingPassword || (!state.passwordForm.old_password || !state.passwordForm.password || !state
          .passwordForm.password_confirmation)"
      class="bg-stone-700 text-stone-50 px-4 py-2 border border-stone-700 hover:bg-stone-800 font-semibold text-sm tracking-wide disabled:opacity-50 disabled:pointer-events-none">
      <span x-text="state.isChangingPassword ? 'Menyimpan...' : 'Simpan Password'"></span>
    </x-shared.button>
  </x-slot:footer>

</x-shared.drawer>
