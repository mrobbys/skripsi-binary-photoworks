@props([
    'recent' => [],
    'getBadgeVariant',
])

{{-- recent reservations table start --}}
<div class="border border-stone-200 bg-stone-50">
  <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-600">Reservasi Terbaru</h3>
    <a
      href="{{ route('backdoor.booking-management.index') }}"
      class="text-xs text-stone-500 underline underline-offset-2 transition hover:text-stone-800"
    >
      Lihat Semua
    </a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-stone-200 bg-stone-100">
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Kode</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Tanggal</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Total</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-200">
        @forelse ($recent as $booking)
          <tr class="transition hover:bg-stone-50">
            <td class="px-4 py-3 font-mono text-xs text-stone-600">{{ $booking->booking_code }}</td>
            <td class="px-4 py-3 text-xs text-stone-600">
              {{ \App\Support\Formatter::dateId($booking->created_at) }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 font-medium text-stone-800">
              {{ \App\Support\Formatter::rupiah($booking->total_price) }}
            </td>
            <td class="px-4 py-3">
              <x-shared.badge
                :value="$booking->status->label()"
                :variant="$getBadgeVariant($booking->status)"
                class="whitespace-nowrap"
              />
            </td>
          </tr>
        @empty
          <tr>
            <td
              colspan="4"
              class="px-4 py-8 text-center text-sm text-stone-400"
            >Belum ada data reservasi</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
{{-- recent reservations table end --}}
