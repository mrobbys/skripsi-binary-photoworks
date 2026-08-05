<footer class="border-t border-stone-200 bg-stone-50 py-16 text-center">
  <div class="mx-auto max-w-4xl px-6">
    {{-- title start --}}
    <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
      Terhubung Dengan Kami
    </h2>
    {{-- title end --}}

    {{-- social media btn start --}}
    <div class="mt-6 flex items-center justify-center gap-5 text-stone-900">
      {{-- instagram start --}}
      <a
        href="https://instagram.com"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Instagram"
        class="transition-colors hover:text-stone-600"
      >
        <i class="ri-instagram-fill text-2xl"></i>
      </a>
      {{-- instagram end --}}

      {{-- facebook start --}}
      <a
        href="https://facebook.com"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Facebook"
        class="transition-colors hover:text-stone-600"
      >
        <i class="ri-facebook-circle-fill text-2xl"></i>
      </a>
      {{-- facebook end --}}

      {{-- twitter start --}}
      <a
        href="https://x.com"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Twitter / X"
        class="transition-colors hover:text-stone-600"
      >
        <i class="ri-twitter-x-fill text-2xl"></i>
      </a>
      {{-- twitter end --}}

      {{-- tiktok start --}}
      <a
        href="https://tiktok.com"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="TikTok"
        class="transition-colors hover:text-stone-600"
      >
        <i class="ri-tiktok-fill text-2xl"></i>
      </a>
      {{-- tiktok end --}}
    </div>
    {{-- social media btn end --}}

    {{-- description start --}}
    <p class="mx-auto mt-6 max-w-xl text-sm leading-relaxed text-stone-600">
      Jika Anda memiliki pertanyaan mengenai hal lain yang belum disebutkan, jangan ragu untuk berdiskusi dengan kami.
    </p>
    {{-- description end --}}

    {{-- action buttons start --}}
    <div class="mt-8 flex flex-col items-center justify-center gap-4">
      {{-- whatsapp btn start --}}
      <x-shared.button
        as="a"
        href="https://wa.me/{{ config('studio.whatsapp') }}"
        target="_blank"
        rel="noopener noreferrer"
        variant="dark"
        size="md"
        class="w-full sm:max-w-xs"
      >
        Hubungi via WhatsApp
      </x-shared.button>
      {{-- whatsapp btn end --}}

      {{-- phone btn start --}}
      <div class="flex w-full max-w-xl flex-col items-center justify-center gap-4 sm:flex-row">
        <x-shared.button
          as="a"
          href="tel:{{ config('studio.whatsapp') }}"
          variant="outline"
          size="md"
          class="w-full font-normal sm:w-auto"
        >
          Phone : {{ config('studio.whatsapp') }}
        </x-shared.button>
        {{-- phone btn end --}}

        {{-- email btn start --}}
        <x-shared.button
          as="a"
          href="mailto:{{ config('studio.email') }}"
          variant="outline"
          size="md"
          class="w-full font-normal sm:w-auto"
        >
          Mail : {{ config('studio.email') }}
        </x-shared.button>
        {{-- email btn end --}}
      </div>
    </div>
    {{-- actions buttons end --}}

    {{-- copyright start --}}
    <div class="mt-16 text-xs text-stone-500">
      Copyright &copy; {{ date('Y') }} {{ config('studio.nama_studio', 'Binary Photoworks') }}
    </div>
    {{-- copyright end --}}
  </div>
</footer>
