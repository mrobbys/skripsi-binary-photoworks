@php
  use App\Domains\Review\Enums\ReviewSort;
@endphp

<div
  x-cloak
  class="flex justify-end pt-2"
>
  {{-- dropdown sort review start --}}
  <div class="w-full sm:w-56">
    <select
      name="sort"
      x-data="choices({ searchEnabled: false, shouldSort: false })"
      x-modelable="value"
      x-model="state.sort"
      x-on:change="onSortChange($el.value)"
      aria-label="Urutkan ulasan"
      class="w-full border border-stone-300 bg-stone-50 px-3.5 py-2 text-sm text-stone-800 focus:border-stone-700 focus:outline-none"
    >
      @foreach (ReviewSort::cases() as $option)
        <option value="{{ $option->value }}">{{ $option->label() }}</option>
      @endforeach
    </select>
  </div>
  {{-- dropdown sort review start --}}
</div>

{{-- Loading State --}}
<template x-if="state.isLoading">
  <div class="space-y-0">
    <template x-for="i in 5" :key="i">
      <x-skeleton.frontdoor-review-item />
    </template>
  </div>
</template>
{{-- loading state end --}}

{{-- empty state start --}}
<template x-if="!state.isLoading && state.items.length === 0">
  <div class="py-12 text-center">
    <p class="text-sm text-stone-500">Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>
  </div>
</template>
{{-- empty state end --}}

{{-- list review start --}}
<template x-if="!state.isLoading && state.items.length > 0">
  <div class="space-y-0">
    <template
      x-for="review in state.items"
      :key="review.id"
    >
      <div class="border-b border-stone-200 py-8">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3
              class="text-xs font-bold uppercase tracking-wider text-stone-900"
              x-text="review.user_name"
            ></h3>
            <div class="mt-1 flex items-center gap-1 text-sm text-stone-700">
              <template
                x-for="n in 5"
                :key="n"
              >
                <i
                  x-bind:class="n <= review.rating ? 'ri-star-fill' : 'ri-star-line text-stone-300'"
                  aria-hidden="true"
                ></i>
              </template>
            </div>
          </div>
          <span
            class="shrink-0 text-xs text-stone-500"
            x-text="review.formatted_time"
          ></span>
        </div>
        <p
          class="mt-3 text-sm leading-relaxed text-stone-700"
          x-text="review.comment"
        ></p>
      </div>
    </template>
  </div>
</template>
{{-- list review end --}}