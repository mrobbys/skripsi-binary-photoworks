<div class="border border-stone-200 bg-stone-50 p-6 md:p-8">
  <div class="grid grid-cols-1 gap-8 md:grid-cols-12">
    {{-- foto paket start --}}
    <x-backdoor.data-master.package.info.image />
    {{-- foto paket end --}}

    {{-- detail teks & keterangan start --}}
    <div class="flex flex-col justify-between md:col-span-8 lg:col-span-9">
      <div>
        <x-backdoor.data-master.package.info.header />

        <div class="mb-6 h-px bg-stone-200"></div>

        <x-backdoor.data-master.package.info.features />
      </div>
    </div>
    {{-- detail teks & keterangan end --}}
  </div>
</div>
