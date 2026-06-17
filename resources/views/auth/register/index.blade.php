<x-layouts.auth.index title='Daftar'>
  <x-slot:content>
    <div>
      <h1 class="text-center my-4 text-xl font-semibold">Form Register</h1>

      <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf

        <div class="w-full">
          <input type="text" name="name" id="name" autocomplete="off" placeholder="your name"
            value="" required>
          @error('name')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <div class="w-full">
          <input type="tel" name="phone" id="phone" autocomplete="off" placeholder="your phone"
            value="" required>
          @error('phone')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <div class="w-full">
          <input type="email" name="email" id="email" autocomplete="off" placeholder="your@email.com"
            value="" required>
          @error('email')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <div class="w-full">
          <input type="password" name="password" id="password" autocomplete="off" placeholder="•••••••"
            value="" required>
          @error('password')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <div class="w-full">
          <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="off"
            placeholder="•••••••" value="" required>
          @error('password_confirmation')
            <div>
              <small class="text-red-500">{{ $message }}</small>
            </div>
          @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-stone-50 w-full py-3 px-4">
          Daftar
        </button>

        {{-- register dengan google --}}
        <a href="{{ route('auth.google') }}" class="bg-stone-400 text-stone-50 w-full py-3 px-4">
          Daftar dengan Google
        </a>

        {{-- link to login page --}}
        <a href="{{ route('login') }}" class="text-center block my-4 text-blue-500 underline">Sudah Punya Akun?
          Login</a>
      </form>
    </div>
  </x-slot:content>

  </x-layouts.auth>
