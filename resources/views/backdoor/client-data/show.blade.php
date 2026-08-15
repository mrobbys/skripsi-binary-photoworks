@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Klien', 'url' => route('backdoor.client-data.index')],
      ['label' => $client->name, 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Klien — {{ $client->name }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/client-data/show/Show"
>
  <x-slot:content>
    <div
      id="show-root"
      data-user-uuid="{{ $client->uuid }}"
      x-data="Show"
      x-cloak
      class="w-full space-y-6"
    >
      {{-- back button & header start --}}
      <x-backdoor.shared.page-header-back
        :href="route('backdoor.client-data.index')"
        title="Detail Klien"
        backLabel="Kembali Ke Data Klien"
      />
      {{-- back button & header end --}}

      <x-backdoor.client-data.profile-card :client="$client" />

      <x-backdoor.client-data.booking-history-table />
    </div>
  </x-slot:content>
</x-layouts.backdoor.index>
