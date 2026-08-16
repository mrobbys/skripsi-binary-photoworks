{{-- modal detail start --}}
<div
  x-show="modal.isOpen"
  x-transition
  x-cloak
  role="dialog"
  aria-modal="true"
  aria-labelledby="modal-detail-title"
  @keydown.escape.window="closeDetail()"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  x-on:click.self="closeDetail()"
>
  <div class="w-full max-w-2xl border border-stone-300 bg-white">

    {{-- header modal start --}}
    <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
      <h2
        id="modal-detail-title"
        class="text-sm font-semibold uppercase tracking-wider text-stone-700"
      >
        Detail Perubahan
      </h2>
      <button
        type="button"
        x-on:click="closeDetail()"
        class="text-stone-400 transition hover:text-stone-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-stone-900"
        aria-label="Tutup modal"
      >
        <i
          class="ri-close-line text-xl"
          aria-hidden="true"
        ></i>
      </button>
    </div>
    {{-- header modal end --}}

    {{-- body modal start --}}
    <div class="p-6">
      <pre
        tabindex="0"
        class="max-h-[60vh] overflow-auto border border-stone-200 bg-stone-100 p-4 font-mono text-xs leading-relaxed text-stone-700 focus:outline-none focus-visible:ring-1 focus-visible:ring-stone-700 sm:max-h-96"
        x-text="formatProperties(modal.properties)"
      ></pre>
    </div>
    {{-- body modal end --}}

    {{-- footer modal start --}}
    <div class="flex justify-end border-t border-stone-200 px-6 py-3">
      <x-shared.button
        x-on:click="closeDetail()"
        variant="primary"
        size="md"
        value="Tutup"
      />
    </div>
    {{-- footer modal end --}}

  </div>
</div>
{{-- modal detail end --}}
