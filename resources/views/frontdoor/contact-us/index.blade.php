<x-layouts.frontdoor.index
  title="Hubungi Kami — Binary Photoworks"
  jsModule="frontdoor/contact-us/Contact"
>
  <x-slot:content>
    <div x-data="Contact">

      {{-- section maps start --}}
      <x-frontdoor.contact-us.maps-section />
      {{-- section maps end --}}

      {{-- section kontak start --}}
      <section class="py-12 lg:py-16">
        <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-2 lg:gap-16">

          {{-- form kontak start --}}
          <x-frontdoor.contact-us.form />
          {{-- form kontak end --}}

          {{-- informasi kontak start --}}
          <div>
            <h2 class="mb-8 font-serif text-3xl font-normal tracking-tight text-stone-900">
              Informasi Kontak
            </h2>

            <div class="space-y-4">

              {{-- lokasi kami start --}}
              <x-frontdoor.contact-us.info-card
                icon="ri-map-pin-line"
                title="Lokasi Kami"
              >
                <p class="text-sm leading-relaxed text-stone-700">
                  {{ config('studio.alamat') }}
                </p>
              </x-frontdoor.contact-us.info-card>
              {{-- lokasi kami end --}}

              {{-- hubungi kami start --}}
              <x-frontdoor.contact-us.info-card
                icon="ri-phone-line"
                title="Hubungi Kami"
              >
                <a
                  href="https://wa.me/{{ config('studio.whatsapp') }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm text-stone-700 underline-offset-2 hover:underline"
                >
                  {{ config('studio.whatsapp') }}
                </a>
              </x-frontdoor.contact-us.info-card>
              {{-- hubungi kami end --}}

              {{-- email resmi start --}}
              <x-frontdoor.contact-us.info-card
                icon="ri-mail-line"
                title="Email Resmi"
              >
                <a
                  href="mailto:{{ config('studio.email') }}"
                  class="text-sm text-stone-700 underline-offset-2 hover:underline"
                >
                  {{ config('studio.email') }}
                </a>
              </x-frontdoor.contact-us.info-card>
              {{-- email resmi end --}}

            </div>
          </div>
          {{-- informasi kontak end --}}

        </div>
      </section>
      {{-- section kontak end --}}

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
