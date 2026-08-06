<x-layouts.frontdoor.index
  title="Ulasan Pelanggan"
  jsModule="frontdoor/review/Index"
>
  <x-slot:content>
    <div
      x-data="Index"
      x-init="init()"
      class="mx-auto max-w-5xl pb-16"
    >

      {{-- header start --}}
      <div class="mb-8 flex flex-col gap-4">
        <h1 class="font-serif text-3xl font-bold tracking-tight text-stone-900">Ulasan</h1>
      </div>
      {{-- header end --}}

      {{-- ringkasan rating start --}}
      <x-frontdoor.review.stats />
      {{-- ringkasan rating end --}}

      {{-- ulasan start --}}
      <div class="space-y-8">
        {{-- ulasan user (berdasarkan yang login) --}}
        @auth
          <x-frontdoor.review.user-review />
        @endauth

        {{-- semua ulasan --}}
        <x-frontdoor.review.list />
      </div>
      {{-- ulasan end --}}

      {{-- pagination start --}}
      <div class="mt-10">
        <x-frontdoor.shared.pagination />
      </div>
      {{-- pagination end --}}

      {{-- modal form start --}}
      @auth
        <x-frontdoor.review.form-modal />
      @endauth
      {{-- modal form end --}}

    </div>
  </x-slot:content>
</x-layouts.frontdoor.index>
