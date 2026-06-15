{{-- 
  * COMPONENT SIDEBAR LINKS

  * Slot : 
      - Berisikan nav item
--}}

{{-- sidebar links start --}}
<nav aria-label="Navigasi Menu Utama">
  <ul class="flex flex-col gap-2 overflow-y-auto pb-6">
    {{ $slot ?? '' }}
  </ul>
</nav>
{{-- sidebar links end --}}
