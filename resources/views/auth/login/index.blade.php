<x-layouts.auth
  title='Masuk'
  js-module='auth/Login'
>
  <x-slot:content>
    {{-- Menginisialisasi komponen Alpine.js 'Login' --}}
    <div x-cloak x-data="Login">
      <h1 class="text-center my-4 text-xl font-semibold">Form Login</h1>

      {{-- 
        Mendengarkan event @submit dan memanggil submitForm() 
        untuk mengubah state.isLoading menjadi true.
        Karena tidak menggunakan preventDefault, form akan tetap melakukan POST ke Laravel.
      --}}
      <form
        method="POST"
        action="{{ route('login.store') }}"
        @submit="submitForm($event)"
        class="space-y-4"
      >
        @csrf
        <div class="w-full">
          <input
            type="email"
            name="email"
            id="email"
            autocomplete="off"
            placeholder="your@email.com"
            value="superadmin@gmail.com"
            required
          >
          @error('email')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        {{-- Container password menggunakan relative agar icon toggle bisa melayang di kanan --}}
        <div class="w-full relative">
          <input
            :type="state.showPassword ? 'text' : 'password'"
            name="password"
            id="password"
            autocomplete="off"
            placeholder="•••••••"
            value="Password1"
            required
            class="w-full pr-10"
          >
          
          {{-- Tombol Toggle Show/Hide Password --}}
          <button
            type="button"
            @click="togglePassword()"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-500 hover:text-stone-700 focus:outline-none"
            aria-label="Tampilkan/Sembunyikan Password"
          >
            {{-- Tampilkan icon mata jika password sedang disembunyikan --}}
            <template x-if="!state.showPassword">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </template>
            {{-- Tampilkan icon mata-dicoret jika password sedang ditampilkan --}}
            <template x-if="state.showPassword">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
              </svg>
            </template>
          </button>

          @error('password')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <div class="flex justify-between items-center">
          <div>
            <input
              type="checkbox"
              name="remember"
              id="remember"
            >
            <label for="remember">Ingat saya</label>
          </div>
          <div>
            <a href="{{ route('forgot.password.index') }}">Lupa Password?</a>
          </div>
        </div>

        {{-- Button submit dengan visual loading state --}}
        <button
          type="submit"
          :disabled="state.isLoading"
          :class="state.isLoading ? 'opacity-70 cursor-not-allowed' : ''"
          class="bg-blue-500 text-stone-50 w-full py-3 px-4 flex items-center justify-center gap-2 transition-all duration-200"
        >
          {{-- Spinner Loading (hanya tampil jika state.isLoading true) --}}
          <template x-if="state.isLoading">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </template>
          
          <span x-text="state.isLoading ? 'Sedang Masuk...' : 'Login'"></span>
        </button>

        {{-- login dengan google --}}
        <a
          href="{{ route('auth.google') }}"
          class="bg-stone-400 text-stone-50 w-full py-3 px-4 text-center block"
        >
          Login dengan Google
        </a>

        {{-- link to register page --}}
        <a
          href="{{ route('register') }}"
          class="text-center block my-4 text-blue-500 underline"
        >Daftar Akun</a>
      </form>
    </div>
  </x-slot:content>

</x-layouts.auth>
