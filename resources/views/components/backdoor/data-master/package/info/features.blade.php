{{-- keterangan global features start --}}
<div x-data="{ open: false }">
  <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-500 md:text-xs">
    Keterangan Global
  </span>
  <ul class="mt-4 grid grid-cols-1 gap-x-8 gap-y-3 xl:grid-cols-2">
    <template
      x-for="(feature, index) in state.packageInfo?.features || []"
      x-bind:key="index"
    >
      <li
        x-show="open || index < 6"
        class="flex items-start gap-2.5 text-sm text-stone-700"
        x-transition
        x-cloak
      >
        <span class="mt-2 size-1.5 shrink-0 bg-stone-400"></span>
        <span x-text="feature"></span>
      </li>
    </template>
    <template x-if="!state.packageInfo?.features || state.packageInfo.features.length === 0">
      <li class="col-span-full text-sm italic text-stone-400">
        Tidak ada keterangan global
      </li>
    </template>
  </ul>

  <template x-if="(state.packageInfo?.features?.length || 0) > 6">
    <div class="mt-4">
      <button
        x-on:click="open = !open"
        type="button"
        class="group inline-flex items-center gap-1 text-xs font-semibold text-stone-500 transition hover:text-stone-900 cursor-pointer"
      >
        <span
          x-text="open ? 'Tampilkan lebih sedikit' : 'Tampilkan selengkapnya'"
          class="group-hover:underline"
        ></span>
        <i
          class="ri-arrow-down-s-line transition-transform duration-200"
          x-bind:class="open ? 'rotate-180' : ''"
          aria-hidden="true"
        ></i>
      </button>
    </div>
  </template>
</div>
{{-- keterangan global features end --}}
