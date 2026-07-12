{{-- 
  Digunakan pada file jadwal.blade.php / Dashboard User
--}}
<div x-cloak class="flex gap-3">
  <x-shared.button
    value="Akan Datang"
    size="sm"
    class="px-5 border"
    x-bind:disabled="state.isLoading"
    x-on:click="switchTab('upcoming')"
    x-bind:class="state.activeTab === 'upcoming' ?
        'bg-stone-500 text-stone-50 border-stone-500' :
        'bg-transparent border-stone-300 text-stone-600 hover:bg-stone-50'" />

  <x-shared.button
    value="Selesai"
    size="sm"
    class="px-5 border"
    x-bind:disabled="state.isLoading"
    x-on:click="switchTab('past')"
    x-bind:class="state.activeTab === 'past' ?
        'bg-stone-500 text-stone-50 border-stone-500' :
        'bg-transparent border-stone-300 text-stone-600 hover:bg-stone-50'" />
</div>
