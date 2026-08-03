<div
  class="border border-stone-200 bg-stone-50 flex flex-col h-full hover:border-stone-300 transition-all duration-300">
  <div class="h-80 overflow-hidden p-6">
    <img x-bind:src="package.image_url || 'https://placehold.co/600x800?text=No+Image'"
      x-bind:alt="package.name"
      class="w-full h-full object-cover hover:scale-105 transition-transform duration-700 ease-in-out">
  </div>
  <div class="px-6 pb-6 flex flex-col flex-1">
    <div class="flex items-center justify-between mb-2">
      <span class="text-[11px] font-semibold text-stone-500 uppercase tracking-widest"
        x-text="package.category_name">
      </span>
      <x-shared.badge value="WA Only" variant="neutral" icon="ri-whatsapp-line" alpine="package.is_whatsapp_only" />
    </div>
    <h3 class="text-2xl font-serif font-bold text-stone-900 mb-1" x-text="package.name"></h3>
    <p class="text-sm text-stone-500 mb-4">
      Mulai dari <span class="font-semibold text-stone-900"
        x-text="package.min_price_formatted"></span>
    </p>
    <div class="mt-auto">
      <x-shared.button
        as="a"
        x-bind:href="`{{ route('frontdoor.booking.flow', ':slug') }}`.replace(':slug', package.slug)"
        variant="primary"
        value="Pilih Layanan"
        class="w-full text-center"
      />
    </div>
  </div>
</div>
