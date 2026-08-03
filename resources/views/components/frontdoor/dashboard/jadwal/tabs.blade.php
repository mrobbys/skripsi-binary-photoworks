{{-- 
  Digunakan pada file jadwal.blade.php / Dashboard User
--}}
<div
  x-cloak
  class="flex gap-3"
>
  <x-shared.button
    value="Akan Datang"
    variant="custom"
    x-bind:disabled="state.isLoading"
    x-on:click="switchTab('upcoming')"
    x-bind:class="state.activeTab === 'upcoming' ?
        'bg-stone-800 text-stone-50 border border-transparent' :
        'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
  />

  <x-shared.button
    value="Selesai"
    variant="custom"
    x-bind:disabled="state.isLoading"
    x-on:click="switchTab('past')"
    x-bind:class="state.activeTab === 'past' ?
        'bg-stone-800 text-stone-50 border border-transparent' :
        'border border-stone-300 bg-stone-50 text-stone-800 hover:bg-stone-100'"
  />
</div>
