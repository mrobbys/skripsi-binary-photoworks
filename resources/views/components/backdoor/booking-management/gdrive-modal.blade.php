<div
  x-show="state.isGdriveOpen"
  x-on:keydown.escape.window="closeGdrive()"
  class="fixed inset-0 z-50 flex items-center justify-center"
  x-cloak
>

  <div
    class="absolute inset-0 bg-stone-900/60"
    x-on:click="closeGdrive()"
  ></div>

  <form
    x-on:submit.prevent="submitGdrive()"
    class="relative z-10 mx-4 w-full max-w-md border border-stone-200 bg-white p-6"
  >
    <h3 class="mb-4 text-base font-bold text-stone-900">Input Link Google Drive</h3>
    <input
      type="url"
      x-model="state.gdriveLink"
      placeholder="https://drive.google.com/..."
      class="w-full border border-stone-300 bg-white px-3 py-2.5 text-sm"
    >
    <small
      class="text-xs text-red-600"
      x-text="state.gdriveErrors.gdriveLink"
      x-show="state.gdriveErrors.gdriveLink"
    ></small>
    <div class="mt-6 flex justify-end gap-4">
      <button
        type="button"
        x-on:click="closeGdrive()"
        class="text-sm font-semibold text-stone-600 hover:text-stone-900"
      >
        Batal
      </button>
      <button
        type="submit"
        x-bind:disabled="state.isGdriveLoading || !state.gdriveLink"
        class="bg-stone-800 px-4 py-2 text-sm text-white disabled:opacity-50"
      >
        Simpan
      </button>
    </div>
  </form>
</div>
