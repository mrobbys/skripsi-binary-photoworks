<x-layouts.frontdoor.index title="Tentang Kami">
  <x-slot:content>
      {{-- section 1 start --}}
      <x-frontdoor.about.story-section />
      {{-- section 1 end --}}

      {{-- section 2 start --}}
      <x-frontdoor.about.team-section :teams="$teams" />
      {{-- section 2 end --}}
  </x-slot:content>
</x-layouts.frontdoor.index>
