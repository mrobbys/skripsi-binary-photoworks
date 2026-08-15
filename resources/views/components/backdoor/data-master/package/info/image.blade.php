{{-- image container start --}}
<div class="md:col-span-4 lg:col-span-3">
  <div class="aspect-4/5 w-full border border-stone-300 bg-stone-200">
    <template x-if="state.packageInfo?.image_url">
      <img
        x-bind:src="state.packageInfo?.image_url"
        x-bind:alt="state.packageInfo?.name"
        class="pointer-events-none h-full w-full select-none object-cover"
      />
    </template>
    <template x-if="!state.packageInfo?.image_url">
      <div class="flex h-full w-full flex-col items-center justify-center gap-2 p-4 text-center text-stone-400">
        <i
          class="ri-image-line text-4xl"
          aria-hidden="true"
        ></i>
        <span class="text-xs font-semibold">Belum ada gambar</span>
      </div>
    </template>
  </div>
</div>
{{-- image container end --}}
