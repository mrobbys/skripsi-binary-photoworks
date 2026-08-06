<x-layouts.frontdoor
  title="Profil Saya - Dashboard"
  jsModule="frontdoor/dashboard/Profile">

  <x-slot:content>
    <div
      class="w-full"
      x-data="Profile"
      x-init="const initialData = {
          name: {{ Js::from(Auth::user()->name) }},
          email: {{ Js::from(Auth::user()->email) }},
          phone: {{ Js::from(Auth::user()->phone) }}
      };
      Object.assign(state, initialData);
      state.originalData = initialData;"
      x-cloak>

      <div class="mx-auto max-w-6xl w-full flex flex-col md:flex-row gap-8">

        <x-frontdoor.dashboard.sidebar />

        <div class="w-full">
          <section class="border border-stone-300 p-6 sm:p-8 space-y-6">

            {{-- header start --}}
            <div class="flex items-center justify-between">
              <h1 class="text-xl font-bold text-stone-900">Profil Saya</h1>
              <x-shared.button
                type="button"
                variant="outline"
                value="Ganti Password"
                x-on:click="openPasswordDrawer()"
              >
                <x-slot:iconLeft>
                  <i class="ri-lock-password-line" aria-hidden="true"></i>
                </x-slot:iconLeft>
              </x-shared.button>
            </div>
            {{-- header end --}}

            <div class="h-px bg-stone-200"></div>

            {{-- form profile start --}}
            <form class="space-y-5 max-w-lg" x-on:submit.prevent="submitUpdateProfile()">

              {{-- name start --}}
              <x-shared.input.field name="name" label="Nama Lengkap" :required="true">
                <x-shared.input.text
                  name="name"
                  x-model="state.name"
                  x-on:input="validateProfileField('name')"
                  x-on:blur="validateProfileField('name')"
                  autocomplete="off"
                />
              </x-shared.input.field>
              {{-- name end --}}

              {{-- email start --}}
              <x-shared.input.field name="email" label="Email" :required="true">
                <x-shared.input.text
                  type="email"
                  name="email"
                  x-model="state.email"
                  x-on:input="validateProfileField('email')"
                  x-on:blur="validateProfileField('email')"
                  autocomplete="off"
                />
              </x-shared.input.field>
              {{-- email end --}}

              {{-- phone start --}}
              <x-shared.input.field name="phone" label="No Telepon (WhatsApp)" :required="true">
                <x-shared.input.text
                  name="phone"
                  x-model="state.phone"
                  x-on:input="validateProfileField('phone')"
                  x-on:blur="validateProfileField('phone')"
                  inputmode="numeric"
                  placeholder="6281243212341"
                  autocomplete="off"
                />
              </x-shared.input.field>
              {{-- phone end --}}

              {{-- tombol simpan --}}
              <div class="pt-2">
                <x-shared.button
                  type="submit"
                  variant="dark"
                  value="Simpan Perubahan"
                  xLoading="state.isUpdatingProfile"
                  loadingText="Menyimpan..."
                  x-bind:disabled="state.isUpdatingProfile || !state.hasChanges"
                  class="w-full"
                />
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
