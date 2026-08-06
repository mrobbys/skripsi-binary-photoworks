{{-- Digunakan di dalam x-for="bg in state.selectedVariant.backgrounds" --}}
<div role="button" tabindex="0" class="flex flex-col items-center gap-2 cursor-pointer w-20 shrink-0 snap-start" 
  x-on:click="state.selectedBackgroundId = bg.id"
  x-on:keydown.enter.prevent="$el.click()"
  x-on:keydown.space.prevent="$el.click()">
  <div class="w-20 h-20 border-2 overflow-hidden transition-colors"
    x-bind:class="state.selectedBackgroundId === bg.id ? 'border-stone-900 ring-2 ring-stone-900 ring-offset-2' : 'border-stone-200'">
    <img x-bind:src="bg.image_url || 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=200'"
      alt="" class="w-full h-full object-cover">
  </div>
  <span class="text-xs text-stone-900 text-center font-medium line-clamp-1" x-text="bg.name"></span>
</div>
