{{--
   * COMPONENT BACKDOOR BREADCRUMBS
   * Digunakan untuk menampilkan navigasi breadcrumbs secara dinamis di dashboard admin

   * Props:
      * `items` : array
        Array berisi daftar navigasi breadcrumb. Contoh format:
        @ php
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Marketing', 'url' => ''], // Item terakhir (aktif) tidak akan di-render sebagai link
        ];
        @ endphp
--}}

@props([
    'items' => [],
])

@if (!empty($items))
  <nav
    class="inline-block text-xs md:text-sm font-medium text-stone-600"
    aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1">
      @foreach ($items as $index => $item)
        @php
          // Cek apakah item saat ini adalah item terakhir
          $isLast = $index === count($items) - 1;
        @endphp

        {{-- jika item bukan item terakhir, maka tampilkan dengan tag a --}}
        @if (!$isLast)
          <li class="flex items-center gap-1">
            <a
              href="{{ $item['url'] ?? '#' }}"
              class="hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-stone-950 focus-visible:outline-offset-2">
              {{ $item['label'] ?? '' }}
            </a>
            <i class="ri-arrow-right-s-line text-base text-stone-400" aria-hidden="true"></i>
          </li>
          {{-- jika item terakhir, maka tampilkan list saja --}}
        @else
          <li
            class="flex items-center gap-1 font-bold text-stone-900"
            aria-current="page">
            {{ $item['label'] ?? '' }}
          </li>
        @endif
      @endforeach
    </ol>
  </nav>
@endif
