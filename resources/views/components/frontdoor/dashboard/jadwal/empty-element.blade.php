<div x-cloak x-show="!state.isLoading && state.appointments.length === 0"
  class="py-8 flex flex-col items-center justify-center ">

  <p class="text-stone-900 font-medium mb-2">Tidak ada jadwal</p>
  <p class="text-sm text-stone-500 mb-8 text-center">
    Anda belum memiliki jadwal sesi foto dalam waktu dekat.
  </p>

  <div class="w-full max-w-xs mx-auto">
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.services.index') }}"
      class="w-full bg-stone-500 text-stone-50 hover:bg-stone-600"
      value="Mulai Sesi Baru" />
  </div>
</div>
