{{--
   * COMPONENT BACKDOOR SIDEBAR OVERLAY
   * Latar gelap ketika sidebar mobile dibuka
--}}

<div
  x-cloak
  x-show="sidebarIsOpen"
  class="fixed inset-0 z-20 bg-stone-950/10 backdrop-blur-xs md:hidden"
  aria-hidden="true"
  x-on:click="sidebarIsOpen = false"
  x-transition.opacity
>
</div>
