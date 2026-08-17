@props([
    'today' => [],
    'getBadgeVariant',
])

{{-- today schedule table start --}}
<div class="border border-stone-200 bg-stone-50">
  <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-stone-600">Jadwal Pemotretan Hari Ini</h3>
    <a
      href="{{ route('backdoor.session-schedule.list') }}"
      class="text-xs text-stone-500 underline underline-offset-2 transition hover:text-stone-800"
    >
      Lihat Semua
    </a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-stone-200 bg-stone-100">
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Waktu</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Klien</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Paket</th>
          <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-600">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-stone-200">
        @forelse ($today as $booking)
          <tr class="transition hover:bg-stone-50">
            <td class="px-4 py-3 text-xs tabular-nums text-stone-700">
              {{ \App\Support\Formatter::timeRange($booking->start_time, $booking->end_time) }}
            </td>
            <td class="px-4 py-3 text-xs font-medium text-stone-800">{{ $booking->user->name }}</td>
            <td class="px-4 py-3 text-stone-600">{{ $booking->packageVariant->package->name }}</td>
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
            >Tidak ada jadwal pemotretan hari ini</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
{{-- today schedule table end --}}
