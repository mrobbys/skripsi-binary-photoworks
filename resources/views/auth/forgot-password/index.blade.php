<x-layouts.auth.index title='Forgot Password'>
  <x-slot:content>
    <div>
      <h1 class="text-center my-4 text-xl font-semibold">
        Forgot Password
      </h1>

      <form
        method="POST"
        action="{{ route('forgot.password.email') }}"
        class="space-y-4">
        @csrf
        <div class="w-full">
          <input
            type="email"
            name="email"
            id="email"
            autocomplete="off"
            placeholder="your@email.com"
            value="robby@gmail.com"
            required>
          @error('email')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <button
          type="submit"
          class="bg-blue-500 text-stone-50 w-full py-3 px-4">
          Kirim link reset password
        </button>

        {{-- link to login page --}}
        <a
          href="{{ route('login') }}"
          class="text-center block my-4 text-blue-500 underline">
          Kembali ke halaman Login
        </a>
      </form>
    </div>
  </x-slot:content>

  </x-layouts.auth>
