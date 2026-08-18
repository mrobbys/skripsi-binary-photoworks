{{-- client info card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Informasi Klien</h2>
  </div>
  <div class="grid grid-cols-1 gap-4 p-5 text-sm md:grid-cols-2">
    <div>
      <p class="mb-1 text-xs text-stone-500">Nama Lengkap</p>
      <p
        class="font-medium text-stone-900"
        x-text="state.booking?.user?.name || '-'"
      ></p>
    </div>
    <div>
      <p class="mb-1 text-xs text-stone-500">Nomor WhatsApp</p>
      <p
        class="font-medium text-stone-900"
        x-text="state.booking?.user?.phone || '-'"
      ></p>
    </div>
    <div class="md:col-span-2">
      <p class="mb-1 text-xs text-stone-500">Email</p>
      <p
        class="font-medium text-stone-900"
        x-text="state.booking?.user?.email || '-'"
      ></p>
    </div>
  </div>
</div>
{{-- client info card end --}}
