@props(['client'])

<div class="border border-stone-200 bg-stone-50 p-4">
  {{-- info profil klien start --}}
  <div class="mb-6 flex flex-col gap-6 sm:flex-row sm:items-start">
    <div class="flex flex-1 flex-col gap-1">
      <h2 class="text-xl font-bold text-stone-900">{{ $client->name }}</h2>
      <p class="text-sm text-stone-500">Bergabung sejak {{ $client->joined_at }}</p>
      <div class="mt-2 flex flex-wrap items-center gap-4 text-sm font-medium text-stone-700">
        <span class="flex items-center gap-1.5">
          <i
            class="ri-mail-line text-lg text-stone-400"
            aria-hidden="true"
          ></i>
          {{ $client->email }}
        </span>
        <span class="flex items-center gap-1.5">
          <i
            class="ri-phone-line text-lg text-stone-400"
            aria-hidden="true"
          ></i>
          {{ $client->phone ?? '-' }}
        </span>
      </div>
    </div>
  </div>
  {{-- info profil klien end --}}

  {{-- grid statistik start --}}
  <div class="grid grid-cols-1 gap-4 border-t border-stone-200 pt-6 sm:grid-cols-2 lg:grid-cols-4">
    {{-- total booking keseluruhan start --}}
    <div class="flex flex-col justify-center bg-stone-100 p-4">
      <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">Total Booking</p>
      <p class="mt-1 text-3xl font-bold text-stone-900">
        {{ $client->total_all }}
        <span class="text-sm font-normal text-stone-500">Sesi</span>
      </p>
    </div>
    {{-- total booking keseluruhan end --}}

    {{-- menunggu pembayaran dan dp start --}}
    <div class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-stone-600">Menunggu Pembayaran</span>
        <span class="border border-yellow-200 bg-yellow-50 px-2 py-0.5 text-xs font-bold text-yellow-600">
          {{ $client->total_pending }}
        </span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-stone-600">DP Terbayar</span>
        <span class="border border-stone-300 bg-stone-200 px-2 py-0.5 text-xs font-bold text-stone-700">
          {{ $client->total_dp }}
        </span>
      </div>
    </div>
    {{-- menunggu pembayaran dan dp end --}}

    {{-- lunas & selesai start --}}
    <div class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4 lg:border-l">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-stone-600">Lunas</span>
        <span class="border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-lime-600">
          {{ $client->total_success }}
        </span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-stone-600">Sesi Selesai</span>
        <span class="border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-lime-600">
          {{ $client->total_done }}
        </span>
      </div>
    </div>
    {{-- lunas & selesai end --}}

    {{-- batal start --}}
    <div class="flex flex-col justify-center gap-3 border-l-0 border-stone-200 pl-0 sm:border-l sm:pl-4">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-stone-600">Dibatalkan</span>
        <span class="border border-red-200 bg-red-50 px-2 py-0.5 text-xs font-bold text-red-600">
          {{ $client->total_cancel }}
        </span>
      </div>
    </div>
    {{-- batal end --}}
  </div>
  {{-- grid statistik end --}}
</div>
