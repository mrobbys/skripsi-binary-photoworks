<x-layouts.auth.index title="Periksa Email Anda">
  <x-slot:content>
    <div>
      <h1 class="text-center my-4 text-xl font-semibold">Cek email anda</h1>

      <form
        method="POST"
        action="{{ route('forgot.password.resend') }}">
        @csrf
        <div class="w-full">
          <button
            type="submit"
            class="bg-blue-500 text-stone-50 w-full py-3 px-4">
            Kirim ulang link reset password
          </button>
        </div>
      </form>

      {{-- link to login page --}}
      <a
        href="{{ route('login') }}"
        class="text-center block my-4 text-blue-500 underline">
        Login
      </a>
    </div>
  </x-slot:content>
  </x-layouts.auth>
