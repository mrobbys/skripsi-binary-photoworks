<x-layouts.auth.index
  title='Masuk'
  jsModule='auth/Login'
>
  <x-slot:content>
    <div
      x-cloak
      x-data="Login"
      class="my-auto flex w-full max-w-5xl items-center justify-center gap-8"
    >

      {{-- section left start --}}
      <div class="flex w-full flex-col justify-between lg:w-1/2">
        <div>
          {{-- logo binary --}}
          <x-auth.logo />

          {{-- title & subtitle --}}
          <x-auth.header
            title="Masuk"
            subtitle="Silakan masuk untuk melanjutkan."
          />

          {{-- form login start --}}
          <form
            method="POST"
            action="{{ route('login.store') }}"
            x-on:submit="submitForm($event)"
            class="space-y-6"
            novalidate
          >
            @csrf

            {{-- input email start --}}
            <x-shared.input.field
              name="email"
              label="EMAIL"
              :required="true"
            >
              <x-shared.input.text
                type="email"
                name="email"
                placeholder="email@address.com"
                x-model="state.form.email"
                x-on:blur="validateField('email')"
                x-on:input="validateField('email')"
                value="{{ old('email') }}"
                required
              />
            </x-shared.input.field>
            {{-- input email end --}}

            {{-- input password start --}}
            <x-shared.input.field
              name="password"
              label="PASSWORD"
              :required="true"
            >
              <x-shared.input.password
                name="password"
                placeholder="Masukkan password Anda"
                x-model="state.form.password"
                x-on:blur="validateField('password')"
                x-on:input="validateField('password')"
                required
              />
            </x-shared.input.field>
            {{-- input password end --}}

            {{-- remember me & forgot password start --}}
            <div class="flex items-center justify-between pt-1 text-xs text-stone-600">
              <label class="flex cursor-pointer select-none items-center gap-2">
                <input
                  type="checkbox"
                  name="remember"
                  id="remember"
                  value="1"
                  x-model="state.form.remember"
                  class="border-stone-300 text-stone-800 focus:ring-0"
                >
                <span>Ingat saya</span>
              </label>
              <a
                href="{{ route('forgot.password.index') }}"
                class="transition-colors hover:text-stone-900"
              >
                Lupa Password?
              </a>
            </div>
            {{-- remember me & forgot password end --}}

            {{-- btn submit login start --}}
            <x-shared.button
              type="submit"
              variant="primary"
              class="w-full py-3"
              xLoading="state.isLoading"
              loadingText="Sedang Masuk..."
              x-bind:disabled="state.isLoading || !state.isFormValid"
            >
              Login
            </x-shared.button>
            {{-- btn submit login end --}}
          </form>
          {{-- form login end --}}

          {{-- divider --}}
          <x-auth.divider />

          {{-- google login button --}}
          <x-auth.google-button text="Masuk dengan Google" />
        </div>

        {{-- footer register link --}}
        <x-auth.footer
          text="Belum punya akun?"
          linkText="Daftar"
          :href="route('register')"
        />
      </div>
      {{-- section left end --}}

      {{-- section right start --}}
      <x-auth.hero />
      {{-- section right end --}}

    </div>
  </x-slot:content>
</x-layouts.auth.index>
