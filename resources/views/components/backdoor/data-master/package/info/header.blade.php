{{-- header info start --}}
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-6">
  <div class="flex-1">
    <span
      class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 md:text-xs"
      x-text="state.packageInfo?.category_name"
    ></span>
    <h3
      class="mt-1 font-heading text-xl font-bold text-stone-900 md:text-2xl"
      x-text="state.packageInfo?.name"
    ></h3>
    <template x-if="state.packageInfo?.description">
      <p
        class="mt-3 max-w-3xl text-sm leading-relaxed text-stone-600"
        x-text="state.packageInfo?.description"
      ></p>
    </template>
  </div>
  <button
    type="button"
    x-on:click="openEditDrawer(state.packageInfo)"
    class="inline-flex w-fit shrink-0 items-center justify-center gap-2 border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-50 active:scale-[0.97] cursor-pointer"
  >
    <i
      class="ri-pencil-fill"
      aria-hidden="true"
    ></i>
    <span>Edit Info Paket</span>
  </button>
</div>
{{-- header info end --}}
