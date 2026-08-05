@props([
    'schedules' => [],
])

<section class="w-full">
  <div class="grid grid-cols-1 items-start gap-12 lg:grid-cols-2 lg:gap-16">

    {{-- kiri start --}}
    <div class="w-full overflow-hidden border border-stone-200">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.540658307856!2d114.83685581112388!3d-3.4611709418578065!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de681eb995150bf%3A0x73d53981b8405a8!2sBinary%20Photoworks%20Studio!5e0!3m2!1sid!2sid!4v1785813167883!5m2!1sid!2sid"
        class="h-[350px] w-full border-0 sm:h-[400px] lg:h-[450px]"
        allowfullscreen
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
        title="Peta lokasi Binary Photoworks Studio, Banjarbaru"
      ></iframe>
    </div>
    {{-- kiri end --}}

    {{-- kanan start --}}
    <div class="flex flex-col space-y-6">
      <h2 class="font-serif text-3xl font-normal tracking-tight text-stone-900 sm:text-4xl">
        Jam Operasional
      </h2>

      {{-- List Jadwal Per Hari --}}
      <div class="divide-y divide-stone-200/80 border-b border-t border-stone-200/80">
        @forelse ($schedules as $schedule)
          <div class="flex items-center justify-between py-3.5 text-sm sm:text-base">
            <span class="font-medium text-stone-700">
              {{ $schedule->day?->label() ?? 'Hari' }}
            </span>
            <span class="{{ $schedule->is_active ? 'font-semibold text-stone-900' : 'font-normal text-stone-500' }}">
              @if ($schedule->is_active)
                {{ $schedule->start_time?->format('H.i') }} - {{ $schedule->end_time?->format('H.i') }}
              @else
                Tutup
              @endif
            </span>
          </div>
        @empty
          <div class="py-4 text-center text-sm text-stone-500">
            Jadwal operasional belum diatur.
          </div>
        @endforelse
      </div>

      {{-- Catatan Bawah --}}
      <p class="text-xs uppercase leading-relaxed tracking-wider text-stone-500">
        *SESI KHUSUS DI LUAR JAM OPERASIONAL DAPAT DIATUR MELALUI CHAT <a href="https://wa.me/{{ config('studio.whatsapp') }}" class="font-semibold text-stone-700 underline">WHATSAPP</a>.
      </p>
    </div>
    {{-- kanan end --}}

  </div>
</section>
