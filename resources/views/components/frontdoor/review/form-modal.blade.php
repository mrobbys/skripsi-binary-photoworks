<div
  x-show="state.openModal"
  x-cloak
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  x-on:keydown.escape.window="resetForm()"
  class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/50 p-4 backdrop-blur-[2px]"
>
  <div
    x-on:click.outside="resetForm()"
    role="dialog"
    aria-modal="true"
    aria-labelledby="review-modal-title"
    class="w-full max-w-lg rounded-none border border-stone-300 bg-stone-50 p-6 md:p-8"
  >
    <div class="mb-4">
      <h2
        id="review-modal-title"
        class="font-serif text-2xl font-bold text-stone-900"
      >Berikan Ulasan Anda</h2>
      <p class="mt-2 text-xs leading-relaxed text-stone-600 md:text-sm">
        Bagikan pengalaman Anda menggunakan layanan Binary Photoworks. Nama Anda akan otomatis tersemat dari akun
        terdaftar.
      </p>
    </div>

    <form
      x-on:submit.prevent="submitReview()"
      class="space-y-6"
    >
      {{-- input star rating start --}}
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-stone-700">
          Rating Bintang
        </label>
        <div
          class="flex items-center gap-2 text-2xl text-stone-700"
          x-on:mouseleave="clearHover()"
        >
          <template
            x-for="n in 5"
            :key="n"
          >
            <button
              type="button"
              x-on:mouseenter="setHover(n)"
              x-on:click="setRating(n)"
              x-bind:aria-label="n + ' Bintang'"
              class="transform transition-transform hover:scale-110 focus:outline-none"
            >
              <i
                x-bind:class="(state.hoverRating ? state.hoverRating >= n : state.form.rating >= n) ?
                'ri-star-fill text-stone-700' :
                'ri-star-line text-stone-300'"
                aria-hidden="true"
              ></i>
            </button>
          </template>
        </div>
      </div>
      {{-- input star rating end --}}

      {{-- input comment start --}}
      <x-shared.input.field
        name="comment"
        label="Ulasan Anda"
        :required="true"
      >
        <x-shared.input.textarea
          name="comment"
          rows="4"
          placeholder="Tuliskan detail ulasan atau masukan Anda di sini..."
          :required="true"
          x-model="state.form.comment"
          x-bind:maxlength="state.maxComment"
          x-on:input="validateField('comment')"
          x-on:blur="validateField('comment')"
        />
      </x-shared.input.field>
      {{-- input comment end --}}

      {{-- actions btn start --}}
      <div class="flex items-center justify-end gap-3 pt-2">
        <x-shared.button
          as="button"
          type="button"
          variant="outline"
          value="Batal"
          x-on:click="resetForm()"
        />
        <x-shared.button
          as="button"
          type="submit"
          variant="primary"
          value="Kirim Ulasan"
          xLoading="state.isLoading"
          loadingText="Mengirim..."
          x-bind:disabled="!state.form.rating || !state.form.comment.trim() || state.isLoading"
        />
      </div>
      {{-- actions btn end --}}
    </form>
  </div>
</div>
