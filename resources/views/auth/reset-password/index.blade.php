<x-layouts.auth.index
  title='Perbarui Kata Sandi'
  jsModule='auth/ResetPassword'
>
  <x-slot:content>
    <div
      x-cloak
      x-data="ResetPassword"
      class="my-auto w-full max-w-md space-y-8"
    >
      <x-auth.header
        title="Perbarui Kata Sandi"
        subtitle="Silakan masukkan email Anda dan buat kata sandi baru untuk mengamankan akun Anda."
        :center="true"
      />

      <form
        method="POST"
        action="{{ route('reset.password.store') }}"
        x-on:submit="submitForm($event)"
        class="space-y-6"
        novalidate
      >
        @csrf
        {{-- hidden token dari URL --}}
        <input
          type="hidden"
          name="token"
          value="{{ $token }}"
        >

        {{-- input email start --}}
        <x-shared.input.field
          name="email"
          label="EMAIL"
          :required="true"
        >
          <x-shared.input.text
            type="email"
            name="email"
            placeholder="nama@email.com"
            x-model="state.form.email"
            x-on:blur="validateField('email')"
            x-on:input="validateField('email')"
            value="{{ old('email') }}"
            required
          />
        </x-shared.input.field>
        {{-- input email end --}}

{{-- input new password start --}}
        <x-shared.input.field
          name="password"
          label="PASSWORD BARU"
          :required="true"
        >
          <x-shared.input.password
            name="password"
            placeholder="Minimal 8 karakter"
            x-model="state.form.password"
            x-on:blur="validateField('password')"
            x-on:input="validateField('password')"
            required
          />
          {{-- list error password --}}
          <ul
            x-cloak
            x-show="state.passwordErrors.length > 0"
            class="mt-1 space-y-0.5 text-xs text-red-600"
          >
            <template
              x-for="err in state.passwordErrors"
              :key="err"
            >
              <li
                x-text="err"
                class="before:mr-1 before:content-['-']"
              ></li>
            </template>
          </ul>
        </x-shared.input.field>
        {{-- input new password end --}}

        {{-- input confirm new password start --}}
        <x-shared.input.field
          name="password_confirmation"
          label="KONFIRMASI PASSWORD BARU"
          :required="true"
        >
          <x-shared.input.password
            name="password_confirmation"
            placeholder="Ulangi password baru"
            x-model="state.form.password_confirmation"
            x-on:blur="validateField('password_confirmation')"
            x-on:input="validateField('password_confirmation')"
            required
          />
        </x-shared.input.field>
        {{-- input confirm new password end --}}

        <x-shared.button
          type="submit"
          variant="primary"
          class="w-full py-3"
          xLoading="state.isLoading"
          loadingText="Memperbarui..."
          x-bind:disabled="state.isLoading || !state.isFormValid"
        >
          PERBARUI PASSWORD
        </x-shared.button>
      </form>

      <div class="border-t border-stone-200 pt-6 text-center">
        <a
          href="{{ route('login') }}"
          class="inline-flex items-center gap-1 text-xs text-stone-500 transition-colors hover:text-stone-900"
        >
          <i class="ri-arrow-left-line"></i>
          Kembali ke halaman Masuk
        </a>
      </div>
    </div>
  </x-slot:content>
</x-layouts.auth.index>
