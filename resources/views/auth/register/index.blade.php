<x-layouts.auth.index
  title='Daftar'
  jsModule='auth/Register'
>
  <x-slot:content>
    <div
      x-cloak
      x-data="Register"
      class="my-auto flex w-full max-w-5xl items-start justify-center gap-8"
    >

      {{-- section left start --}}
      <div class="flex w-full flex-col justify-between lg:w-1/2">
        <div>
          {{-- logo binary --}}
          <x-auth.logo />

          {{-- title & subtitle --}}
          <x-auth.header
            title="Daftar"
            subtitle="Buat akun baru untuk mulai memesan."
          />

          {{-- form register start --}}
          <form
            method="POST"
            action="{{ route('register.store') }}"
            x-on:submit="submitForm($event)"
            class="space-y-6"
            novalidate
          >
            @csrf

            {{-- input name start --}}
            <x-shared.input.field
              name="name"
              label="NAMA LENGKAP"
              :required="true"
            >
              <x-shared.input.text
                name="name"
                placeholder="Masukkan nama lengkap Anda"
                x-model="state.form.name"
                x-on:blur="validateField('name')"
                x-on:input="validateField('name')"
                value="{{ old('name') }}"
                required
              />
            </x-shared.input.field>
            {{-- input name end --}}

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

            {{-- input phone start --}}
            <x-shared.input.field
              name="phone"
              label="NOMOR TELEPON"
              :required="true"
            >
              <x-shared.input.text
                name="phone"
                placeholder="62xxxxxxxxxxx"
                x-model="state.form.phone"
                x-on:blur="validateField('phone')"
                x-on:input="validateField('phone')"
                value="{{ old('phone') }}"
                required
              />
            </x-shared.input.field>
            {{-- input phone end --}}

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
              {{-- pesan validasi password dalam bentuk array/list --}}
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
            {{-- input password end --}}

            {{-- input password_confirmation start --}}
            <x-shared.input.field
              name="password_confirmation"
              label="KONFIRMASI PASSWORD"
              :required="true"
            >
              <x-shared.input.password
                name="password_confirmation"
                placeholder="Masukkan ulang password Anda"
                x-model="state.form.password_confirmation"
                x-on:blur="validateField('password_confirmation')"
                x-on:input="validateField('password_confirmation')"
                required
              />
            </x-shared.input.field>
            {{-- input password_confirmation end --}}

            {{-- btn submit register start --}}
            <x-shared.button
              type="submit"
              variant="primary"
              class="w-full py-3"
              xLoading="state.isLoading"
              loadingText="Mendaftar..."
              x-bind:disabled="state.isLoading || !state.isFormValid"
            >
              DAFTAR
            </x-shared.button>
            {{-- btn submit register end --}}
          </form>
          {{-- form register end --}}

          {{-- divider --}}
          <x-auth.divider />

          {{-- google register button --}}
          <x-auth.google-button text="Daftar dengan Google" />
        </div>

        {{-- footer login link --}}
        <x-auth.footer
          text="Sudah punya akun?"
          linkText="Masuk"
          :href="route('login')"
        />
      </div>
      {{-- section left end --}}

      {{-- section right start --}}
      <x-auth.hero />
      {{-- section right end --}}

    </div>
  </x-slot:content>
</x-layouts.auth.index>
