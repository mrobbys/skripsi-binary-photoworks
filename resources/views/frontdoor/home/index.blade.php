<x-layouts.frontdoor.index title="Home" jsModule="frontdoor/home/Home">
  <x-slot:content>
    <div x-data="Home" class="space-y-24 pb-24">
      {{-- hero start --}}
      <x-frontdoor.home.hero-section />
      {{-- hero end --}}

      {{-- layanan kami start --}}
      <x-frontdoor.home.services-section :packages="$packages" />
      {{-- layanan kami start --}}

      {{-- why choose us start --}}
      <x-frontdoor.home.why-choose-section />
      {{-- why choose us end --}}

      {{-- portfolio section start --}}
      <x-frontdoor.home.portfolio-section />
      {{-- portfolio section end --}}

      {{-- section ulasan klien start --}}
      <x-frontdoor.home.reviews-section :reviews="$reviews" />
      {{-- section ulasan klien end --}}

      {{-- section jam operasional start --}}
      <x-frontdoor.home.schedules-section :schedules="$schedules" />
      {{-- section jam operasional end --}}

      {{-- section faq start --}}
      <x-frontdoor.home.faq-section />
      {{-- section faq end --}}
    </div>
  </x-slot:content>

</x-layouts.frontdoor.index>
