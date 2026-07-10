<x-layouts.frontdoor title="Profil Saya - Dashboard">
  <x-slot:content>
    <div class="w-full h-full min-h-[calc(100vh-80px)] bg-stone-50 py-12 px-4 sm:px-6">
      <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8">
        
        <x-frontdoor.dashboard.sidebar />

        <main class="flex-1">
          <div class="bg-white border border-stone-200 p-6 sm:p-8">
            <h1 class="text-xl font-bold text-stone-900 mb-8">Profil Saya</h1>
            
            <form action="#" method="POST" class="space-y-6 max-w-lg">
              @csrf
              @method('PUT')
              
              <div>
                <label for="name" class="block text-sm font-medium text-stone-700 uppercase mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ Auth::user()->name ?? 'Robby Setiawan' }}" class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-stone-700 uppercase mb-2">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ Auth::user()->email ?? 'rubi@gmail.com' }}" class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
              </div>

              <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 uppercase mb-2">No Telepon <span class="text-red-500">*</span></label>
                <input type="text" id="phone" name="phone" value="{{ Auth::user()->phone ?? '' }}" class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
              </div>

              <div class="pt-4">
                <x-shared.button type="submit" class="w-full bg-stone-700 hover:bg-stone-800 text-stone-50 py-3">
                  Simpan Perubahan
                </x-shared.button>
              </div>
            </form>
          </div>
        </main>

      </div>
    </div>
  </x-slot:content>
</x-layouts.frontdoor>
