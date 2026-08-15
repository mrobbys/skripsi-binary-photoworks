{{--
  * COMPONENT: SHARED IMAGE PREVIEW MODAL
  * Modal popup full screen untuk melihat preview gambar original.
  *
  * Props:
  *   - openState   : Alpine expression untuk status buka/tutup (default: 'state.isPreviewOpen')
  *   - closeAction : Alpine expression untuk fungsi tutup (default: 'closeImagePreview()')
  *   - imageUrl    : Alpine expression untuk URL gambar (default: 'state.previewImageUrl')
  *   - imageName   : Alpine expression untuk caption/nama gambar (default: 'state.previewImageName')
--}}

@props([
    'openState' => 'state.isPreviewOpen',
    'closeAction' => 'closeImagePreview()',
    'imageUrl' => 'state.previewImageUrl',
    'imageName' => 'state.previewImageName',
])

<template x-teleport="body">
  <div
    x-show="{{ $openState }}"
    x-on:keydown.escape.window="{{ $closeAction }}"
    class="fixed inset-0 z-100 flex items-center justify-center bg-stone-950/85 p-4"
    x-cloak
  >
    <div
      x-on:click.self="{{ $closeAction }}"
      class="relative w-full max-w-3xl"
    >
      {{-- tombol tutup start --}}
      <button
        type="button"
        x-on:click="{{ $closeAction }}"
        class="absolute -top-10 right-0 text-stone-300 transition hover:text-white cursor-pointer"
        aria-label="Tutup preview"
      >
        <i
          class="ri-close-line text-3xl"
          aria-hidden="true"
        ></i>
      </button>
      {{-- tombol tutup end --}}

      {{-- container gambar start --}}
      <div class="overflow-hidden border border-stone-700 bg-stone-900">
        <img
          x-bind:src="{{ $imageUrl }}"
          x-bind:alt="'Preview ' + ({{ $imageName }} || 'Gambar')"
          class="h-auto max-h-[80vh] w-full object-contain"
        />
        <div class="border-t border-stone-700 px-4 py-3 text-center">
          <span
            class="text-sm font-medium text-stone-300"
            x-text="{{ $imageName }}"
          ></span>
        </div>
      </div>
      {{-- container gambar end --}}
    </div>
  </div>
</template>
