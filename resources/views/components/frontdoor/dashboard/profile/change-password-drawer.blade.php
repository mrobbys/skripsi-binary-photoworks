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
    <x-shared.input.field name="old_password" label="Password Lama" :required="true">
      <x-shared.input.password
        name="old_password"
        x-model="state.passwordForm.old_password"
        x-on:input="validatePasswordField('old_password')"
        x-on:blur="validatePasswordField('old_password')"
        placeholder="Masukkan password lama"
      />
    </x-shared.input.field>
    {{-- password lama end --}}

    <div class="h-px bg-stone-200"></div>

    {{-- password baru --}}
    <x-shared.input.field name="password" label="Password Baru" :required="true">
      <x-shared.input.password
        name="password"
        x-model="state.passwordForm.password"
        x-on:input="validatePasswordField('password')"
        x-on:blur="validatePasswordField('password')"
        placeholder="Min. 8 karakter, huruf kecil, huruf besar & angka"
      />
      <ul
        x-cloak
        x-show="state.passwordErrors.length > 0"
        class="mt-1 space-y-0.5 text-xs text-red-600"
      >
        <template x-for="error in state.passwordErrors" :key="error">
          <li x-text="error" class="before:mr-1 before:content-['-']"></li>
        </template>
      </ul>
    </x-shared.input.field>

    {{-- konfirmasi password baru start --}}
    <x-shared.input.field name="password_confirmation" label="Konfirmasi Password Baru" :required="true">
      <x-shared.input.password
        name="password_confirmation"
        x-model="state.passwordForm.password_confirmation"
        x-on:input="validatePasswordField('password_confirmation')"
        x-on:blur="validatePasswordField('password_confirmation')"
        placeholder="Ulangi password baru"
      />
    </x-shared.input.field>
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
      x-bind:disabled="state.isChangingPassword || (!state.passwordForm.old_password || !state.passwordForm.password || !state.passwordForm.password_confirmation) || state.passwordErrors.length > 0"
    />
  </x-slot:footer>

</x-shared.drawer>
