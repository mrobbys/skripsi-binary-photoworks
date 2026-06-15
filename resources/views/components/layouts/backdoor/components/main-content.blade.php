{{--
   * COMPONENT BACKDOOR MAIN CONTENT
   * Wadah konten utama untuk dashboard admin
--}}

<main
  id="main-content"
  class="p-8 focus:outline-hidden"
  tabindex="-1"
>
  <div class="overflow-y-auto">
    {{ $slot }}
  </div>
</main>
