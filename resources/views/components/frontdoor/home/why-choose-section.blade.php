<section class="w-full">
  <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">

    {{-- kiri start --}}
    <div class="relative order-2 h-[450px] w-full overflow-hidden sm:h-[550px] lg:order-1 lg:h-[650px]">
      <img
        src="{{ asset('assets/images/why-choose-us.webp') }}"
        alt="Fotografer profesional sedang mengambil foto model di studio"
        class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
        loading="lazy"
      />
    </div>
    {{-- kiri end --}}

    {{-- kanan start --}}
    <div class="order-1 flex flex-col space-y-8 lg:order-2">
      <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
        Mengapa Memilih Binary?
      </h2>

      <div class="space-y-6 sm:space-y-8">
        <x-frontdoor.home.why-choose-item
          title="Authenticity & Tranquility"
          description="Kami menangkap setiap momen kegembiraan Anda dengan keaslian dan ketenangan untuk menciptakan kenangan terbaik."
        />

        <x-frontdoor.home.why-choose-item
          title="The Most Comfortable Experience"
          description="Fokus utama kami adalah memberikan pengalaman layanan paling nyaman yang pernah Anda rasakan."
        />

        <x-frontdoor.home.why-choose-item
          title="Timeless Remembrance"
          description="Memberikan hasil karya yang abadi, sebuah kenangan yang dapat Anda nikmati kembali kapan saja."
        />

        <x-frontdoor.home.why-choose-item
          title="Flexible & Specialized Services"
          description="Layanan kami fleksibel, mulai dari sesi studio kilat hingga dokumentasi acara besar."
        />
      </div>
    </div>
    {{-- kanan end --}}

  </div>
</section>
