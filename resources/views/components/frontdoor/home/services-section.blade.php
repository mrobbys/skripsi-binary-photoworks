@props([
    'packages' => [],
])

<section class="w-full">
  <div class="mb-18 text-center">
    <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
      Layanan Kami
    </h2>
  </div>

  <div class="grid grid-cols-1 gap-8 md:grid-cols-3 lg:gap-10">
    @forelse ($packages as $package)
      <x-frontdoor.home.service-card :package="$package" />
    @empty
      <div class="col-span-full py-8 text-center text-stone-500">
        Belum ada paket layanan yang tersedia.
      </div>
    @endforelse
  </div>

  <div class="mt-10 text-center">
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.services.index') }}"
      variant="outline"
      size="md"
      value="Lihat Semua Layanan"
      class="hover:scale-105"
    />
  </div>
</section>
