{{-- Sidebar --}}
<aside class="w-full md:w-80 shrink-0">
  <div class="border border-stone-300 p-6">
    <div class="mb-8">
      <h2 class="text-xl font-bold text-stone-900">{{ Auth::user()->name ?? '' }}</h2>
      <p class="text-sm text-stone-600">{{ Auth::user()->email ?? '' }}</p>
    </div>

    <nav class="flex flex-col gap-2">
      <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}"
        class="justify-start w-full py-2.5 {{ request()->routeIs('frontdoor.dashboard.index') ? 'bg-stone-200 text-stone-900' : 'text-stone-700 hover:bg-stone-100' }}">
        <x-slot:iconLeft>
          <i class="ri-calendar-event-line text-xl"></i>
        </x-slot:iconLeft>
        Jadwal Saya
      </x-shared.button>

      <x-shared.button as="a" href="{{ route('frontdoor.dashboard.profil') }}"
        class="justify-start w-full py-2.5 {{ request()->routeIs('frontdoor.dashboard.profil') ? 'bg-stone-200 text-stone-900' : 'text-stone-700 hover:bg-stone-100' }}">
        <x-slot:iconLeft>
          <i class="ri-user-line text-xl"></i>
        </x-slot:iconLeft>
        Profil Saya
      </x-shared.button>

      <form method="POST" action="{{ route('logout') }}" class="mt-4"
        x-data
        x-on:submit.prevent="
          confirmModal('Konfirmasi Logout', 'Sesi Anda akan diakhiri. Yakin ingin keluar sekarang?', 'question', 'Ya, Keluar')
            .then((result) => {
              if (result.isConfirmed) {
                $el.submit();
              }
            })
        ">
        @csrf
        <x-shared.button type="submit" class="justify-start w-full py-2.5 text-red-600 hover:bg-red-50 focus:ring-red-200">
          <x-slot:iconLeft>
            <i class="ri-logout-box-r-line text-xl"></i>
          </x-slot:iconLeft>
          Keluar
        </x-shared.button>
      </form>
    </nav>
  </div>
</aside>
