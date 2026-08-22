@props([
    'reviews' => [],
])

<section class="w-full">
  <div data-animate="reviews-header" class="mb-18 text-center">
    <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
      Ulasan Klien
    </h2>
  </div>

  <div id="reviews-grid" class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
    @forelse ($reviews as $review)
      <x-frontdoor.home.review-card :review="$review" />
    @empty
      <div class="col-span-full py-8 text-center text-stone-500">
        Belum ada ulasan dari pelanggan.
      </div>
    @endforelse
  </div>

  <div data-animate="reviews-footer" class="mt-10 text-center">
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.reviews') }}"
      variant="outline"
      size="md"
      value="Lihat Semua Ulasan"
      class="hover:scale-105"
    />
  </div>
</section>
