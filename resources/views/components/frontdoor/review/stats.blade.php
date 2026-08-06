<div
  x-cloak
  class="mx-auto mb-12 max-w-3xl border border-stone-200 bg-stone-50/50 p-6 md:p-8"
>
  <div class="grid grid-cols-1 gap-8 md:grid-cols-12 md:items-center">

    {{-- bar start --}}
    <div class="space-y-2 md:col-span-6 md:border-r md:border-stone-200 md:pr-8">
      <template
        x-for="star in [5, 4, 3, 2, 1]"
        :key="star"
      >
        <div class="flex items-center text-xs font-medium text-stone-700">
          <span
            class="w-6 shrink-0 text-right"
            x-text="`${star} ★`"
          ></span>
          <div class="mx-3 h-2.5 flex-1 overflow-hidden bg-stone-200">
            <div
              class="h-full bg-stone-600 transition-all duration-500"
              x-bind:style="`width: ${state.stats.breakdown_percentage[star] ?? 0}%`"
            ></div>
          </div>
          <span
            class="w-6 shrink-0 text-stone-500"
            x-text="state.stats.breakdown[star] ?? 0"
          ></span>
        </div>
      </template>
    </div>
    {{-- bar end --}}

    <div class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center md:col-span-6 md:pl-4">
      <div>
        {{-- average rating start --}}
        <div
          class="font-serif text-4xl font-bold tracking-tight text-stone-900"
          x-text="state.stats.average_rating"
        ></div>
        {{-- average rating end --}}

        {{-- star rating start --}}
        <div class="my-1.5 flex items-center gap-1 text-lg text-stone-700">
          <template
            x-for="n in 5"
            :key="n"
          >
            <i
              :class="n <= Math.round(state.stats.average_rating) ? 'ri-star-fill' : 'ri-star-line text-stone-300'"
              aria-hidden="true"
            ></i>
          </template>
        </div>
        {{-- star rating end --}}

        {{-- total review start --}}
        <p
          class="text-xs font-medium text-stone-500"
          x-text="`${state.stats.total_reviews} Ulasan`"
        ></p>
        {{-- total review end --}}
      </div>

      {{-- open modal start --}}
      @auth
        <div>
          <x-shared.button
            as="button"
            type="button"
            x-on:click="state.openModal = true"
            variant="primary"
            value="Tulis Ulasan"
            x-bind:disabled="state.isLoading"
          />
        </div>
      @endauth
      {{-- open modal end --}}
    </div>

  </div>
</div>
