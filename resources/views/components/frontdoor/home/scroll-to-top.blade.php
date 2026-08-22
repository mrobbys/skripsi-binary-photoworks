<div
  x-data="{ visible: false }"
  x-init="visible = window.scrollY > 400"
  x-on:scroll.window.throttle.50ms="visible = window.scrollY > 400"
  x-cloak
>
  <button
    type="button"
    x-on:click="window.scrollTo({ top: 0, behavior: 'smooth' })"
    class="fixed bottom-6 right-6 z-40 flex h-10 w-10 items-center justify-center border border-stone-800 bg-stone-900 text-stone-100 transition-all duration-300 ease-out hover:bg-stone-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-900 focus-visible:ring-offset-2 sm:bottom-8 sm:right-8 sm:h-11 sm:w-11"
    x-bind:class="visible
        ?
        'opacity-100 translate-y-0 pointer-events-auto cursor-pointer' :
        'opacity-0 translate-y-4 pointer-events-none'"
    aria-label="Kembali ke atas"
    title="Kembali ke atas"
  >
    <i class="ri-arrow-up-line text-lg sm:text-xl"></i>
  </button>
</div>
