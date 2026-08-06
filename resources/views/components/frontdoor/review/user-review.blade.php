<template x-if="state.userReview && !state.isLoading">
  <div class="border border-stone-300 bg-stone-100/70 p-6">
    <div class="mb-4 flex items-center justify-between">
      <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500">Ulasan Anda</span>
      {{-- btn delete start --}}
      <button
        type="button"
        x-on:click="deleteReview(state.userReview.id)"
        class="text-red-400 transition hover:text-red-600 focus:outline-none cursor-pointer"
        title="Hapus ulasan"
      >
        <i
          class="ri-delete-bin-line text-base"
          aria-hidden="true"
        ></i>
      </button>
      {{-- btn delete end --}}
    </div>

    {{-- five star rating start --}}
    <div class="flex items-center gap-1 text-sm text-stone-700">
      <template
        x-for="n in 5"
        :key="n"
      >
        <i
          x-bind:class="n <= state.userReview.rating ? 'ri-star-fill' : 'ri-star-line text-stone-300'"
          aria-hidden="true"
        ></i>
      </template>
    </div>
    {{-- five star rating end --}}

    {{-- komentar start --}}
    <p
      class="mt-2 text-sm leading-relaxed text-stone-700"
      x-text="state.userReview.comment"
    ></p>
    {{-- komentar end --}}

    {{-- waktu komentar start --}}
    <p
      class="mt-2 text-xs text-stone-400"
      x-text="state.userReview.formatted_time"
    ></p>
    {{-- waktu komentar start --}}
  </div>
</template>
