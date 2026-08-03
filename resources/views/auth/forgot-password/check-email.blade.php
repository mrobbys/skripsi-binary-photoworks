<x-layouts.auth.index
  title='Periksa Email Anda'
  jsModule='auth/CheckEmail'
>
  <x-slot:content>
    <div
      x-cloak
      x-data="CheckEmail"
      class="my-auto w-full max-w-md space-y-8 text-center"
    >
      {{-- icon mail start--}}
      <div class="flex justify-center">
        <i class="ri-mail-send-line text-5xl text-stone-400"></i>
      </div>
      {{-- icon mail end --}}

      <div>
        <h1 class="font-serif text-3xl font-normal tracking-tight text-stone-900">
          Periksa Email Anda
        </h1>
        <p class="mt-3 text-sm text-stone-500">
          Tautan untuk mengatur ulang kata sandi telah dikirim ke email Anda.
          Silakan periksa kotak masuk atau folder spam.
        </p>
      </div>

      {{-- kembali ke login start --}}
      <x-shared.button
        as="a"
        variant="outline"
        class="w-full py-3"
        :href="route('login')"
      >
        Kembali ke halaman Masuk
      </x-shared.button>
      {{-- kembali ke login start --}}

      {{-- resend start --}}
      <p class="text-xs text-stone-500">
        Belum menerima email?
        <button
          type="button"
          x-on:click="resend()"
          x-bind:disabled="state.isResending"
          class="font-bold text-stone-900 underline transition-colors hover:text-stone-700 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer"
        >
          <span x-text="state.isResending ? 'Mengirim...' : 'Kirim Ulang'"></span>
        </button>
      </p>
      {{-- resend end --}}
    </div>
  </x-slot:content>
</x-layouts.auth.index>
