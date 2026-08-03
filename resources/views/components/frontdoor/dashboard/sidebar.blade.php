{{-- Sidebar --}}
<aside class="w-full shrink-0 md:w-80">
  <div class="border border-stone-300 p-6">
    <div class="mb-8">
      <h2 class="text-xl font-bold text-stone-900">{{ Auth::user()->name ?? '' }}</h2>
      <p class="text-sm text-stone-600">{{ Auth::user()->email ?? '' }}</p>
    </div>

    <nav class="flex flex-col gap-2">
      <x-shared.button
        as="a"
        href="{{ route('frontdoor.dashboard.index') }}"
        :variant="request()->routeIs('frontdoor.dashboard.index') ? 'secondary' : 'ghost'"
        value="Jadwal Saya"
        class="w-full justify-start"
      >
        <x-slot:iconLeft>
          <i class="ri-calendar-event-line text-xl"></i>
        </x-slot:iconLeft>
      </x-shared.button>

      <x-shared.button
        as="a"
        href="{{ route('frontdoor.dashboard.profile') }}"
        :variant="request()->routeIs('frontdoor.dashboard.profile') ? 'secondary' : 'ghost'"
        value="Profil Saya"
        class="w-full justify-start"
      >
        <x-slot:iconLeft>
          <i class="ri-user-line text-xl"></i>
        </x-slot:iconLeft>
      </x-shared.button>

      <form
        method="POST"
        action="{{ route('logout') }}"
        class="mt-4"
        x-data
        x-on:submit.prevent="
          confirmModal('Konfirmasi Logout', 'Sesi Anda akan diakhiri. Yakin ingin keluar sekarang?', 'question', 'Ya, Keluar')
            .then((result) => {
              if (result.isConfirmed) {
                $el.submit();
              }
            })
        "
      >
        @csrf
        <x-shared.button
          type="submit"
          variant="ghost"
          value="Keluar"
          class="w-full justify-start text-red-600 hover:bg-red-50 hover:text-red-700"
        >
          <x-slot:iconLeft>
            <i class="ri-logout-box-r-line text-xl"></i>
          </x-slot:iconLeft>
        </x-shared.button>
      </form>
    </nav>
  </div>
</aside>
