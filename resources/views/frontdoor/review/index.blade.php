@php
  use App\Domains\Review\Enums\ReviewSort;
@endphp

<x-layouts.frontdoor.index
  title="Ulasan Pelanggan"
  jsModule="frontdoor/review/Index"
>
  <x-slot:content>
    <div
      x-data="Index"
      x-init="init()"
      class="mx-auto max-w-5xl px-4 pb-16 pt-8"
    >

      {{-- header start --}}
      <div class="mb-8 flex flex-col gap-4">
        <div>
          <h1 class="font-serif text-3xl font-bold tracking-tight text-stone-900">Ulasan</h1>
        </div>
      </div>
      {{-- header end --}}

      {{-- ringkasan rating start --}}
      <div x-cloak class="mx-auto mb-12 max-w-3xl border border-stone-200 bg-stone-50/50 p-6 md:p-8">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-12 md:items-center">

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
                    :style="`width: ${state.stats.breakdown_percentage[star] ?? 0}%`"
                  ></div>
                </div>
                <span
                  class="w-6 shrink-0 text-stone-500"
                  x-text="state.stats.breakdown[star] ?? 0"
                ></span>
              </div>
            </template>
          </div>

          <div
            class="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center md:col-span-6 md:pl-4">
            <div>
              <div
                class="font-serif text-4xl font-bold tracking-tight text-stone-900"
                x-text="state.stats.average_rating"
              ></div>
              <div class="my-1.5 flex items-center gap-1 text-lg text-stone-700">
                <template
                  x-for="n in 5"
                  :key="n"
                >
                  <i
                    :class="n <= Math.round(state.stats.average_rating) ? 'ri-star-fill' : 'ri-star-line text-stone-300'"></i>
                </template>
              </div>
              <p
                class="text-xs font-medium text-stone-500"
                x-text="`${state.stats.total_reviews} Ulasan`"
              ></p>
            </div>

            @auth
              <div>
                <button
                  type="button"
                  @click="state.openModal = true"
                  class="border border-stone-600 bg-stone-700 px-6 py-3 text-xs font-bold uppercase tracking-wider text-stone-50 transition hover:bg-stone-800 focus:outline-none"
                >
                  Tulis Ulasan
                </button>
              </div>
            @endauth
          </div>

        </div>
      </div>
      {{-- ringkasan rating end --}}

      {{-- ulasan start --}}
      <div class="space-y-8">

        @auth
          <template x-if="state.userReview && !state.isLoading">
            <div class="border border-stone-300 bg-stone-100/70 p-6">
              <div class="mb-4 flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-widest text-stone-500">Ulasan Anda</span>
                <button
                  type="button"
                  @click="deleteReview(state.userReview.id)"
                  class="text-stone-400 transition hover:text-red-600 focus:outline-none"
                  title="Hapus ulasan"
                >
                  <i
                    class="ri-delete-bin-line text-base"
                    aria-hidden="true"
                  ></i>
                </button>
              </div>

              <div class="flex items-center gap-1 text-sm text-stone-700">
                <template
                  x-for="n in 5"
                  :key="n"
                >
                  <i :class="n <= state.userReview.rating ? 'ri-star-fill' : 'ri-star-line text-stone-300'"></i>
                </template>
              </div>

              <p
                class="mt-2 text-sm leading-relaxed text-stone-700"
                x-text="state.userReview.comment"
              ></p>

              <p
                class="mt-2 text-xs text-stone-400"
                x-text="state.userReview.formatted_time"
              ></p>
            </div>
          </template>
        @endauth

        <div x-cloak class="flex justify-end pt-2">
          <div class="w-full sm:w-56">
            <select
              name="sort"
              x-data="choices({ searchEnabled: false, shouldSort: false })"
              @change="state.sort = $el.value"
              class="w-full border border-stone-300 bg-stone-50 px-3.5 py-2 text-sm text-stone-800 focus:border-stone-700 focus:outline-none"
            >
              @foreach (ReviewSort::cases() as $option)
                <option value="{{ $option->value }}">{{ $option->label() }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Loading State --}}
        <template x-if="state.isLoading">
          <div class="py-12 text-center">
            <p class="text-sm text-stone-500">Memuat ulasan...</p>
          </div>
        </template>

        {{-- Empty State --}}
        <template x-if="!state.isLoading && state.items.length === 0">
          <div class="py-12 text-center">
            <p class="text-sm text-stone-500">Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>
          </div>
        </template>

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
                        <i :class="n <= review.rating ? 'ri-star-fill' : 'ri-star-line text-stone-300'"></i>
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

      </div>
      {{-- ulasan end --}}

      {{-- Pagination Component --}}
      <div class="mt-10">
        <x-frontdoor.shared.pagination />
      </div>

      {{-- modal form start --}}
      @auth
        <div
          x-show="state.openModal"
          x-cloak
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          @keydown.escape.window="resetForm()"
          class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/50 p-4 backdrop-blur-[2px]"
        >
          {{-- Modal Box Container --}}
          <div
            @click.outside="resetForm()"
            class="w-full max-w-lg rounded-none border border-stone-300 bg-stone-50 p-6 md:p-8"
          >
            <div class="mb-4">
              <h2 class="font-serif text-2xl font-bold text-stone-900">Berikan Ulasan Anda</h2>
              <p class="mt-2 text-xs leading-relaxed text-stone-600 md:text-sm">
                Bagikan pengalaman Anda menggunakan layanan Binary Photoworks. Nama Anda akan otomatis tersemat dari akun
                terdaftar.
              </p>
            </div>

            <form
              @submit.prevent="submitReview()"
              class="space-y-6"
            >
              {{-- Star Rating Interactive Input --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-stone-700">
                  Rating Bintang
                </label>
                <div
                  class="flex items-center gap-2 text-2xl text-stone-700"
                  @mouseleave="clearHover()"
                >
                  <template
                    x-for="n in 5"
                    :key="n"
                  >
                    <button
                      type="button"
                      @mouseenter="setHover(n)"
                      @click="setRating(n)"
                      class="transform transition-transform hover:scale-110 focus:outline-none"
                    >
                      <i
                        :class="(state.hoverRating ? state.hoverRating >= n : state.rating >= n) ?
                        'ri-star-fill text-stone-700' :
                        'ri-star-line text-stone-300'"></i>
                    </button>
                  </template>
                </div>
              </div>

              {{-- Textarea Comment Input --}}
              <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-700">
                  Ulasan Anda
                </label>
                <textarea
                  x-model="state.comment"
                  :maxlength="state.maxComment"
                  rows="4"
                  placeholder="Tuliskan detail ulasan atau masukan Anda di sini..."
                  class="w-full resize-none rounded-none border border-stone-300 bg-white p-3.5 text-sm text-stone-900 placeholder-stone-400 transition focus:border-stone-700 focus:outline-none"
                ></textarea>
                <div class="mt-1.5 text-right text-xs text-stone-400">
                  <span x-text="state.comment.length"></span> / <span x-text="state.maxComment"></span>
                </div>
              </div>

              {{-- Modal Actions --}}
              <div class="flex items-center justify-end gap-3 pt-2">
                <button
                  type="button"
                  @click="resetForm()"
                  class="rounded-none border border-stone-300 bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-stone-700 transition hover:bg-stone-100 focus:outline-none"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="!state.rating || !state.comment.trim() || state.isSubmitting"
                  class="rounded-none border border-stone-700 bg-stone-700 px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-stone-50 transition hover:bg-stone-800 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                >
                  <span x-text="state.isSubmitting ? 'Mengirim...' : 'Kirim Ulasan'"></span>
                </button>
              </div>
            </form>
          </div>
        </div>
      @endauth
      {{-- modal form end --}}

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
