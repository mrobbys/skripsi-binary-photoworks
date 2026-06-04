<x-layouts.auth.index title="Perbarui Kata Sandi">
    <x-slot:content>
        <div>
            <h1 class="text-center my-4 text-xl font-semibold">Perbarui Kata Sandi</h1>

            <form method="POST" action="{{ route('reset.password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="w-full">
                    <input type="email" name="email" placeholder="nama@email.com" required>
                    @error('email')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="w-full">
                    <input type="password" name="password" placeholder="Minimal 8 karakter" required>
                    @error('password')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="w-full">
                    <input type="password" name="password_confirmation" placeholder="Ulangi password baru" required>
                </div>

                <button type="submit" class="bg-blue-500 text-stone-50 w-full py-3 px-4">PERBARUI PASSWORD</button>
            </form>

            <a href="{{ route('login') }}" class="text-center block my-4 text-blue-500 underline">← Kembali ke halaman Masuk</a>
        </div>
    </x-slot:content>
</x-layouts.auth>
