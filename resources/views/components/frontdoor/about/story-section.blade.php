<section
  aria-labelledby="story-heading"
  class="py-16"
>
  <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">

    {{-- studio image start --}}
    <div class="overflow-hidden">
      <img
        src="{{ asset('assets/images/about-studio.png') }}"
        alt="Studio foto Binary Photoworks — ruang pemotretan dengan lighting profesional"
        class="h-full w-full object-cover"
        loading="lazy"
      />
    </div>
    {{-- studio image end --}}

    {{-- kisah & misi start --}}
    <div class="space-y-10">

      {{-- kisah kami start --}}
      <div class="space-y-4">
        <h1
          id="story-heading"
          class="font-serif text-3xl font-normal tracking-tight text-stone-900"
        >
          Kisah Kami
        </h1>
        <p class="leading-relaxed text-stone-600">
          Berawal dari sebuah pengamatan sederhana terhadap cepatnya arus informasi digital,
          BINARY PHOTOWORKS hadir untuk mengembalikan esensi fotografi sebagai bentuk seni yang tenang.
          Kami percaya bahwa setiap piksel memiliki bobot sejarah, dan setiap bidikan adalah upaya
          untuk mengarsipkan waktu dalam bentuk yang paling murni dan tanpa gangguan.
        </p>
      </div>
      {{-- kisah kami end --}}

      {{-- misi kami start --}}
      <div class="space-y-4">
        <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900">
          Misi Kami
        </h2>
        <p class="leading-relaxed text-stone-600">
          Misi kami adalah menciptakan ekosistem visual di mana kejernihan teknis bertemu dengan
          kedalaman emosional. Kami berdedikasi untuk melayani fotografer profesional dan kolektor
          yang memandang fotografi bukan sekadar gambar, melainkan sebuah warisan visual yang perlu
          dijaga dengan penuh ketelitian dan niat yang tulus.
        </p>
      </div>
      {{-- misi kami end --}}

    </div>
    {{-- kisah & misi end --}}

  </div>
</section>
