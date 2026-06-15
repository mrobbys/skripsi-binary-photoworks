{{--
   * COMPONENT BACKDOOR BREADCRUMBS
   * Digunakan untuk menampilkan navigasi breadcrumbs secara dinamis di dashboard admin

   * Props:
      * `items` : array
        Array berisi daftar navigasi breadcrumb. Contoh format:
        [
            ['label' => 'Dashboard', 'url' => '/dashboard'],
            ['label' => 'Marketing', 'url' => ''], // Item terakhir (aktif) tidak akan di-render sebagai link
        ]
--}}

@props([
    'items' => [],
])

@if (!empty($items))
  <nav
    class="hidden md:inline-block text-sm font-medium text-neutral-600"
    aria-label="Breadcrumb"
  >
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
              class="hover:text-neutral-900 focus-visible:outline focus-visible:outline-neutral-950"
            >
              {{ $item['label'] ?? '' }}
            </a>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              stroke="currentColor"
              fill="none"
              stroke-width="2"
              class="size-4"
              aria-hidden="true"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m8.25 4.5 7.5 7.5-7.5 7.5"
              />
            </svg>
          </li>
          {{-- jika item terakhir, maka tampilkan list saja --}}
        @else
          <li
            class="flex items-center gap-1 font-bold text-neutral-900"
            aria-current="page"
          >
            {{ $item['label'] ?? '' }}
          </li>
        @endif
      @endforeach
    </ol>
  </nav>
@endif
