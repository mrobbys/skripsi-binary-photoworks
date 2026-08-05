@props(['icon', 'title'])

<div class="border border-stone-200 px-5 py-5">
  <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-stone-500">
    <i
      class="{{ $icon }} text-base"
      aria-hidden="true"
    ></i>
    {{ $title }}
  </p>
  {{ $slot }}
</div>
