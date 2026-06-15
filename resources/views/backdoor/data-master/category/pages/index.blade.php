@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('backdoor.dashboard')],
    ['label' => 'Kategori Foto', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index title="Kategori Foto" :breadcrumbs="$breadcrumbs">
  <x-slot:content>
    <h1>Kategori Foto</h1>
  </x-slot:content>

</x-layouts.backdoor.index>
