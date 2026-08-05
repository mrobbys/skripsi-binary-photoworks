@props(['package'])

<a
  href="{{ route('frontdoor.booking.flow', $package->slug) }}"
  class="group block transition-transform duration-300"
>
  <div class="aspect-3/4 relative w-full overflow-hidden bg-stone-100">
    <img
      src="{{ $package->getFirstMediaUrl('package-image') }}"
      alt=""
      class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105 grayscale"
      loading="lazy"
    />
  </div>

  <div class="border border-stone-200 bg-stone-50/50 py-4 text-center">
    <h3 class="font-serif text-xl font-normal text-stone-900 transition-colors group-hover:text-stone-700">
      {{ $package->name }}
    </h3>
  </div>
</a>
