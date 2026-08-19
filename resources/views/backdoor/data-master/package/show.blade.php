@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Data Master', 'url' => '#'],
      ['label' => 'Kelola Paket & Varian', 'url' => route('backdoor.data-master.package.index')],
      ['label' => 'Detail Paket', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Paket"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/master-data/package/ShowPackage"
>

  <x-slot:content>
    <div
      class="w-full space-y-6"
      x-data="ShowPackage"
      x-init="initData('{{ $package->slug }}')"
      x-cloak
    >
      {{-- page header start --}}
      <x-backdoor.shared.page-header-back
        :href="route('backdoor.data-master.package.index')"
        backLabel="Kembali Ke Kelola Paket & Varian"
      >
        Detail Paket: <span x-text="state.packageInfo?.name"></span>
      </x-backdoor.shared.page-header-back>
      {{-- page header end --}}

      {{-- info paket start --}}
      <x-backdoor.data-master.package.package-info-card />
      {{-- info paket end --}}

      {{-- table daftar varian start --}}
      <x-backdoor.data-master.package.variant-table />
      {{-- table daftar varian end --}}

      {{-- drawer form edit package start --}}
      @can('packageVariant-master-update')
        <x-backdoor.data-master.package.package-drawer-form :categories="$categories" />
      @endcan
      {{-- drawer form edit package end --}}

      {{-- drawer form variant start --}}
      @canany(['packageVariant-master-create', 'packageVariant-master-update'])
        <x-backdoor.data-master.package.variant-drawer-form />
      @endcanany
      {{-- drawer form variant end --}}

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
