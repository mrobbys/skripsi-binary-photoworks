<div
  class="flex h-full flex-col border border-stone-200 bg-stone-50 transition-colors duration-300 hover:border-stone-400"
>
  <div class="h-80 overflow-hidden p-6">
    <img
      x-bind:src="package.image_url || 'https://placehold.co/600x800?text=No+Image'"
      x-bind:alt="package.name"
      loading="lazy"
      class="h-full w-full object-cover object-center transition-transform duration-500 hover:scale-105"
    >
  </div>
  <div class="flex flex-1 flex-col px-6 pb-6">
    <div class="mb-2 flex items-center justify-between">
      <span
        class="text-[11px] font-semibold uppercase tracking-widest text-stone-500"
        x-text="package.category_name"
      >
      </span>
      <x-shared.badge
        value="WA Only"
        variant="neutral"
        icon="ri-whatsapp-line"
        alpine="package.is_whatsapp_only"
      />
    </div>
    <h3
      class="mb-1 font-serif text-2xl font-bold text-stone-900"
      x-text="package.name"
    ></h3>
    <p class="mb-4 text-sm text-stone-500">
      Mulai dari <span
        class="font-semibold text-stone-900"
        x-text="package.min_price_formatted"
      ></span>
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
