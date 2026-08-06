@props(['variant'])

<div role="button" tabindex="0" class="block border cursor-pointer transition-colors relative"
  x-data="{ open: false }"
  x-on:close-all-features.window="open = false"
  x-on:keydown.enter.prevent="$el.click()"
  x-on:keydown.space.prevent="$el.click()"
  x-bind:class="state.selectedVariantId === {{ $variant->id }} ?
      'border-stone-300 bg-stone-100' :
      'border-stone-200 bg-white hover:border-stone-300'"
  x-on:click="$dispatch('close-all-features'); selectVariant({{ Js::from($variant->loadMissing('features')) }})">

  <div class="p-6">
    <div class="flex justify-between items-start gap-4">
      <div>
        <div class="flex flex-wrap items-center gap-2.5">
          {{-- nama variant dan harga --}}
          <span class="font-semibold text-stone-900 text-lg">
            {{ $variant->name }} · {{ \App\Support\Formatter::rupiah($variant->price) }}
          </span>
          {{-- durasi variant --}}
          <x-shared.badge value="{{ $variant->duration }} Menit" variant="secondary" />
          {{-- tampilkan badge jika whatsapp only --}}
          @if ($variant->is_whatsapp_only)
            <x-shared.badge value="WA Only" variant="neutral" icon="ri-whatsapp-line" />
          @endif
        </div>

        <div class="mt-4">
          <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-stone-500">
            {{-- list fasilitas / keterangan --}}
            @foreach ($variant->features as $index => $feature)
              <li x-show="open || {{ $index }} < 3" class="flex items-center gap-2.5" x-cloak>
                <span class="size-1.5 bg-stone-400 shrink-0"></span>
                <span>{{ $feature->description }}</span>
              </li>
            @endforeach
          </ul>
          @if ($variant->features->count() > 3)
            {{-- tampilkan jika jumlah fasilitas lebih dari 3 --}}
            <button type="button" x-on:click.stop.prevent="open = !open"
              class="text-xs font-semibold text-stone-500 hover:text-stone-900 transition mt-3 cursor-pointer inline-flex items-center gap-1 group">
              <span x-text="open ? 'Sembunyikan' : 'Lihat {{ $variant->features->count() - 3 }} fasilitas lainnya'"
                class="group-hover:underline"></span>
              <i class="ri-arrow-down-s-line transition-transform" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true"></i>
            </button>
          @endif
        </div>
      </div>

      {{-- icon check start --}}
      <div class="shrink-0 size-7 border-2 flex items-center justify-center transition-colors mt-0.5"
        x-bind:class="state.selectedVariantId === {{ $variant->id }} ? 'border-stone-900 bg-stone-900' : 'border-stone-200 bg-white'">
        <i class="ri-check-line text-white text-lg leading-none"
          x-show="state.selectedVariantId === {{ $variant->id }}" x-cloak aria-hidden="true"></i>
      </div>
      {{-- icon check end --}}
    </div>
  </div>
</div>
