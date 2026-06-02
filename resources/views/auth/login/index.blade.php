<x-layouts.auth title='Masuk' feature-name='auth' page-name='login'>
    <x-slot:content>
        <div>
            <h1 class="text-center my-4 text-xl font-semibold">Form Login</h1>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div class="w-full">
                    <input type="email" name="email" id="email" autocomplete="off" placeholder="your@email.com"
                        value="superadmin@gmail.com" required>
                    @error('email')
                        <div>
                            <small class="text-red-500">{{ $message }}</small>
                        </div>
                    @enderror
                </div>
                <div class="w-full">
                    <input type="password" name="password" id="password" autocomplete="off" placeholder="•••••••"
                        value="Password1" required>
                    @error('password')
                        <div>
                            <small class="text-red-500">{{ $message }}</small>
                        </div>
                    @enderror
                </div>
                <div>
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="bg-blue-500 text-stone-50 w-full py-3 px-4">
                    Login
                </button>

                {{-- login dengan google --}}
                <a href="{{ route('auth.google') }}" class="bg-stone-400 text-stone-50 w-full py-3 px-4">
                    Login dengan Google
                </a>

                {{-- link to register page --}}
                <a href="{{ route('register') }}" class="text-center block my-4 text-blue-500 underline">Daftar Akun</a>
            </form>
        </div>
    </x-slot:content>

</x-layouts.auth>
