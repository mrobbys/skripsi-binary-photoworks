<x-layouts.frontdoor title="Jadwal Saya - Dashboard">
  <x-slot:content>
    <div class="w-full h-full min-h-[calc(100vh-80px)] bg-stone-50 py-12 px-4 sm:px-6">
      <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-8">
        
        <x-frontdoor.dashboard.sidebar />

        <main class="flex-1">
          <div class="bg-white border border-stone-200 p-6 sm:p-8">
            <h1 class="text-xl font-bold text-stone-900 mb-6">Jadwal Sesi Foto Anda</h1>
            
            <div class="flex gap-4 mb-8" x-data="{ statusTab: 'akan-datang' }">
              <button x-on:click="statusTab = 'akan-datang'"
                x-bind:class="statusTab === 'akan-datang' ? 'bg-stone-200 text-stone-900' : 'border border-stone-200 text-stone-700 hover:bg-stone-50'"
                class="px-6 py-2 font-medium transition-colors">
                Akan Datang
              </button>
              <button x-on:click="statusTab = 'selesai'"
                x-bind:class="statusTab === 'selesai' ? 'bg-stone-200 text-stone-900' : 'border border-stone-200 text-stone-700 hover:bg-stone-50'"
                class="px-6 py-2 font-medium transition-colors">
                Selesai
              </button>
            </div>

            <div class="space-y-4">
              {{-- Card Item 1 --}}
              <div class="border border-stone-200 p-6 flex flex-col sm:flex-row gap-4 sm:gap-12 hover:border-stone-300 transition-colors bg-stone-50">
                <div class="sm:w-1/3">
                  <p class="font-medium text-stone-900 mb-2">Senin, 25 Mei 2026</p>
                  <p class="text-stone-600">10:00 - 10:30 WITA</p>
                </div>
                <div class="flex-1 sm:border-l sm:border-stone-200 sm:pl-8">
                  <p class="font-medium text-stone-900 mb-2">Studio Wisuda Paket 1</p>
                  <p class="text-stone-600 text-sm flex flex-wrap gap-x-4 gap-y-2 items-center">
                    <span>Sesi 1 Jam</span>
                    <span class="w-1.5 h-1.5 bg-stone-400"></span>
                    <span>DP Terbayar</span>
                    <span class="w-1.5 h-1.5 bg-stone-400"></span>
                    <span>Background Putih</span>
                  </p>
                </div>
              </div>
              
              {{-- Card Item 2 --}}
              <div class="border border-stone-200 p-6 flex flex-col sm:flex-row gap-4 sm:gap-12 hover:border-stone-300 transition-colors bg-stone-50">
                <div class="sm:w-1/3">
                  <p class="font-medium text-stone-900 mb-2">Senin, 25 Mei 2026</p>
                  <p class="text-stone-600">10:00 - 10:30 WITA</p>
                </div>
                <div class="flex-1 sm:border-l sm:border-stone-200 sm:pl-8">
                  <p class="font-medium text-stone-900 mb-2">Studio Wisuda Paket 1</p>
                  <p class="text-stone-600 text-sm flex flex-wrap gap-x-4 gap-y-2 items-center">
                    <span>Sesi 1 Jam</span>
                    <span class="w-1.5 h-1.5 bg-stone-400"></span>
                    <span>DP Terbayar</span>
                    <span class="w-1.5 h-1.5 bg-stone-400"></span>
                    <span>Background Putih</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </main>

      </div>
    </div>
  </x-slot:content>
</x-layouts.frontdoor>
