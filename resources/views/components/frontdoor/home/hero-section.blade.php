<section class="w-full">
  <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-8">

    {{-- left start --}}
    <div class="flex flex-col items-start space-y-6">
      <h1 class="font-serif text-4xl font-normal leading-tight tracking-tight text-stone-900 sm:text-5xl lg:text-6xl">
        Abadikan Momen Berharga Anda
      </h1>

      <p class="max-w-lg text-lg leading-relaxed text-stone-600">
        Studio fotografi profesional untuk mengabadikan setiap detail cerita Anda.
      </p>

      <div class="pt-2">
        <x-shared.button
          as="a"
          href="{{ route('frontdoor.services.index') }}"
          variant="primary"
          size="lg"
        >
          Lihat Katalog
        </x-shared.button>
      </div>
    </div>
    {{-- left end --}}

    {{-- right start --}}
    <div class="relative h-[400px] w-full overflow-hidden sm:h-[500px] lg:h-[600px]">
      <img
        src="{{ asset('assets/images/frontdoor-hero.webp') }}"
        alt="Keluarga bahagia berfoto di studio"
        class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
        loading="eager"
        fetchpriority="high"
      >
    </div>
    {{-- right end --}}

  </div>
</section>
