<x-layouts.frontdoor
  title="Profil Saya - Dashboard"
  jsModule="frontdoor/dashboard/Profile">

  <x-slot:content>
    <div
      class="w-full py-12"
      x-data="Profile"
      x-init="const initialData = {
          name: {{ Js::from(Auth::user()->name) }},
          email: {{ Js::from(Auth::user()->email) }},
          phone: {{ Js::from(Auth::user()->phone) }}
      };
      Object.assign(state, initialData);
      state.originalData = initialData;"
      x-cloak>

      <div class="mx-auto flex flex-col md:flex-row gap-8">

        <x-frontdoor.dashboard.sidebar />

        <div class="w-full">
          <section class="border border-stone-300 p-6 sm:p-8 space-y-6">

            {{-- header start --}}
            <div class="flex items-center justify-between">
              <h1 class="text-xl font-bold text-stone-900">Profil Saya</h1>
              <x-shared.button
                type="button"
                x-on:click="openPasswordDrawer()"
                class="w-auto px-4 py-1.5 text-sm font-bold bg-transparent text-stone-900 border-[1.5px] border-stone-900 hover:bg-stone-900 hover:text-stone-50 transition-colors">
                <i class="ri-lock-password-line mr-1.5"></i>
                Ganti Password
              </x-shared.button>
            </div>
            {{-- header end --}}

            <div class="h-px bg-stone-200"></div>

            {{-- form profile start --}}
            <form class="space-y-5 max-w-lg" x-on:submit.prevent="submitUpdateProfile()">

              {{-- name start --}}
              <div>
                <label for="name" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" x-model="state.name" autocomplete="off"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.name">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.name[0]"></p>
                </template>
              </div>
              {{-- name end --}}

              {{-- email start --}}
              <div>
                <label for="email" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" x-model="state.email" autocomplete="off"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.email">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.email[0]"></p>
                </template>
              </div>
              {{-- email end --}}

              {{-- phone start --}}
              <div>
                <label for="phone" class="block text-sm font-medium text-stone-700 uppercase mb-2">
                  No Telepon (WhatsApp) <span class="text-red-500">*</span>
                </label>
                <input type="text" id="phone" x-model="state.phone" inputmode="numeric"
                  autocomplete="off" placeholder="6281243212341"
                  class="w-full border border-stone-200 px-4 py-2.5 focus:outline-none focus:border-stone-700 focus:ring-1 focus:ring-stone-700 transition-colors">
                <template x-if="state.profileErrors.phone">
                  <p class="mt-1.5 text-sm text-red-600" x-text="state.profileErrors.phone[0]"></p>
                </template>
              </div>
              {{-- phone end --}}

              {{-- tombol simpan --}}
              <div class="pt-2">
                <x-shared.button
                  type="submit"
                  x-bind:disabled="state.isUpdatingProfile || !state.hasChanges"
                  class="w-full bg-stone-800 text-stone-50 py-3 hover:bg-stone-900 transition-colors disabled:opacity-50 disabled:pointer-events-none disabled:cursor-not-allowed">
                  <span x-text="state.isUpdatingProfile ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                </x-shared.button>
              </div>

            </form>
            {{-- form profile end --}}

          </section>
        </div>

      </div>

      {{-- drawer form ganti password start --}}
      <x-frontdoor.dashboard.profile.change-password-drawer />
      {{-- drawer form ganti password end --}}

    </div>
  </x-slot:content>

</x-layouts.frontdoor>
