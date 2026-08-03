<x-layouts.auth.index
  title='Lupa Password'
  jsModule='auth/ForgotPassword'
>
  <x-slot:content>
    <div
      x-cloak
      x-data="ForgotPassword"
      class="my-auto w-full max-w-md space-y-8"
    >
      <x-auth.header
        title="Lupa Password"
        subtitle="Masukkan alamat email Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi."
        :center="true"
      />

      <form
        method="POST"
        action="{{ route('forgot.password.email') }}"
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

        {{-- btn submit start --}}
        <x-shared.button
          type="submit"
          variant="primary"
          class="w-full py-3"
          xLoading="state.isLoading"
          loadingText="Mengirim..."
          x-bind:disabled="state.isLoading || !state.isFormValid"
        >
          KIRIM TAUTAN RESET
        </x-shared.button>
        {{-- btn submit end --}}
      </form>

      <div class="border-t border-stone-200 pt-6 text-center">
        <a
          href="{{ route('login') }}"
          class="inline-flex items-center gap-1 text-sm text-stone-500 transition-colors hover:text-stone-900"
        >
          <i class="ri-arrow-left-line"></i>
          Kembali ke halaman Masuk
        </a>
      </div>
    </div>
  </x-slot:content>
</x-layouts.auth.index>
