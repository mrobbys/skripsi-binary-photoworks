<x-layouts.frontdoor.index
  title="Hubungi Kami — Binary Photoworks"
  jsModule="frontdoor/contact-us/Contact"
>
  <x-slot:content>
    <div
      class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
      x-data="Contact"
    >

      {{-- ==================== SECTION: GOOGLE MAPS ==================== --}}
      <section
        aria-label="Lokasi studio Binary Photoworks"
        class="py-12 pb-0"
      >
        <div class="overflow-hidden border border-stone-200">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.540658307856!2d114.83685581112388!3d-3.4611709418578065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de681eb995150bf%3A0x73d53981b8405a8!2sBinary%20Photoworks%20Studio!5e0!3m2!1sid!2sid!4v1785813167883!5m2!1sid!2sid"
            class="h-80 w-full lg:h-96"
            style="border: 0;"
            allowfullscreen
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
            title="Peta lokasi Binary Photoworks Studio, Banjarbaru"
          ></iframe>
        </div>
      </section>

      {{-- ==================== SECTION: FORM + INFO ==================== --}}
      <section class="py-12 lg:py-16">
        <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-2 lg:gap-16">

          {{-- ===== KIRI: Form Kontak ===== --}}
          <div>
            <h1 class="mb-8 font-serif text-3xl font-normal tracking-tight text-stone-900">
              Form Kontak
            </h1>

            <form
              @submit.prevent="submit"
              class="space-y-5"
            >
              @csrf

              {{-- Nama Lengkap --}}
              <x-shared.input.field
                name="nama"
                label="Nama Lengkap"
                :required="true"
              >
                <x-shared.input.text
                  name="nama"
                  placeholder="John Doe"
                  :required="true"
                  value="{{ old('nama') }}"
                  x-model="state.form.nama"
                  @input="validateField('nama')"
                />
              </x-shared.input.field>

              {{-- Alamat Email --}}
              <x-shared.input.field
                name="email"
                label="Alamat Email"
                :required="true"
              >
                <x-shared.input.text
                  name="email"
                  type="email"
                  placeholder="name@email.com"
                  :required="true"
                  value="{{ old('email') }}"
                  x-model="state.form.email"
                  @input="validateField('email')"
                />
              </x-shared.input.field>

              {{-- Subjek --}}
              <x-shared.input.field
                name="subjek"
                label="Subjek"
                :required="true"
              >
                <x-shared.input.text
                  name="subjek"
                  placeholder="Kerjasama Photography"
                  :required="true"
                  value="{{ old('subjek') }}"
                  x-model="state.form.subjek"
                  @input="validateField('subjek')"
                />
              </x-shared.input.field>

              {{-- Pesan --}}
              <x-shared.input.field
                name="pesan"
                label="Pesan Anda"
                :required="true"
              >
                <x-shared.input.textarea
                  name="pesan"
                  rows="5"
                  placeholder="Tuliskan pesan Anda di sini..."
                  :required="true"
                  x-model="state.form.pesan"
                  @input="validateField('pesan')"
                >{{ old('pesan') }}</x-shared.input.textarea>
              </x-shared.input.field>

              {{-- Tombol Submit --}}
              <div class="pt-2">
                <x-shared.button
                  type="submit"
                  variant="primary"
                  size="lg"
                  class="w-full"
                  x-bind:disabled="state.isLoading"
                >
                  <span x-text="state.isLoading ? 'Mengirim...' : 'Kirim Pesan'"></span>
                </x-shared.button>
              </div>
            </form>
          </div>

          {{-- ===== KANAN: Informasi Kontak ===== --}}
          <div>
            <h2 class="mb-8 font-serif text-3xl font-normal tracking-tight text-stone-900">
              Informasi Kontak
            </h2>

            <div class="space-y-4">

              {{-- Lokasi Kami --}}
              <div class="border border-stone-200 px-5 py-5">
                <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-stone-500">
                  <i
                    class="ri-map-pin-line text-base"
                    aria-hidden="true"
                  ></i>
                  Lokasi Kami
                </p>
                <p class="text-sm leading-relaxed text-stone-700">
                  {{ config('studio.alamat') }}
                </p>
              </div>

              {{-- Hubungi Kami --}}
              <div class="border border-stone-200 px-5 py-5">
                <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-stone-500">
                  <i
                    class="ri-phone-line text-base"
                    aria-hidden="true"
                  ></i>
                  Hubungi Kami
                </p>
                <a
                  href="https://wa.me/{{ config('studio.whatsapp') }}"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-sm text-stone-700 underline-offset-2 hover:underline"
                >
                  {{ config('studio.whatsapp') }}
                </a>
              </div>

              {{-- Email Resmi --}}
              <div class="border border-stone-200 px-5 py-5">
                <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-stone-500">
                  <i
                    class="ri-mail-line text-base"
                    aria-hidden="true"
                  ></i>
                  Email Resmi
                </p>
                <a
                  href="mailto:{{ config('studio.email') }}"
                  class="text-sm text-stone-700 underline-offset-2 hover:underline"
                >
                  {{ config('studio.email') }}
                </a>
              </div>

            </div>
          </div>

        </div>
      </section>

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
