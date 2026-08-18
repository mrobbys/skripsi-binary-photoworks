{{-- gdrive form card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Link Google Drive</h2>
  </div>
  <form
    class="space-y-4 p-5"
    x-on:submit.prevent="submitGdrive()"
  >
    {{-- input link gdrive start --}}
    <x-shared.input.field
      name="gdrive_link"
      label="Tautan Folder Foto"
    >
      <x-shared.input.text
        type="url"
        name="gdrive_link"
        placeholder="https://drive.google.com/..."
        x-model="state.gdriveLink"
        x-on:blur="validateField('gdrive_link')"
        x-on:input="validateField('gdrive_link')"
        x-bind:disabled="state.isGdriveLoading || ['Menunggu', 'DP Terbayar', 'Batal'].includes(state.booking?.status)"
        class="disabled:cursor-not-allowed disabled:bg-stone-50 disabled:opacity-50"
      />
    </x-shared.input.field>
    {{-- input link gdrive end --}}

    {{-- checkbox wa notification start --}}
    <label class="flex cursor-pointer items-start gap-2 select-none">
      <input
        type="checkbox"
        x-model="state.sendWaNotificationGdrive"
        class="mt-1 h-4 w-4 cursor-pointer border-stone-300 text-stone-800 focus:ring-stone-500"
      >
      <div class="flex flex-col">
        <span class="text-sm font-semibold text-stone-900">Kirim Notifikasi WhatsApp</span>
        <span class="text-xs text-stone-500">Kirim tautan Google Drive ke nomor WhatsApp klien</span>
      </div>
    </label>
    {{-- checkbox wa notification end --}}

    {{-- submit button start --}}
    <x-shared.button
      type="submit"
      variant="charcoal"
      size="md"
      class="w-full"
      x-bind:disabled="state.isGdriveLoading || !state.gdriveLink || Boolean(state.errors.gdrive_link) || (state.booking?.gdrive_link === state.gdriveLink) || ['Menunggu', 'DP Terbayar', 'Batal'].includes(state.booking?.status)"
    >
      <span x-text="state.isGdriveLoading ? 'Menyimpan Link...' : 'Simpan Link'"></span>
    </x-shared.button>
    {{-- submit button end --}}
  </form>
</div>
{{-- gdrive form card end --}}
