@props(['packages' => [], 'backgrounds' => []])

{{-- package selection card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Detail Paket</h2>
  </div>
  <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

    {{-- pilih paket start --}}
    <div>
      <label
        for="package_id"
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
      >
        Paket <span class="text-red-500">*</span>
      </label>
      <select
        id="package_id"
        name="package_id"
        x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Paket ---' })"
        x-modelable="value"
        x-model="state.packageId"
      >
        <option value="">--- Pilih Paket ---</option>
        @foreach ($packages as $pkg)
          <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
        @endforeach
      </select>
      <template x-if="state.errors['package_id']">
        <p
          class="mt-1.5 text-xs text-red-600"
          x-text="Array.isArray(state.errors['package_id']) ? state.errors['package_id'][0] : state.errors['package_id']"
        ></p>
      </template>
    </div>
    {{-- pilih paket end --}}

    {{-- pilih varian start --}}
    <div>
      <label
        for="package_variant_id"
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
      >
        Varian <span class="text-red-500">*</span>
      </label>
      <select
        id="package_variant_id"
        name="package_variant_id"
        x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Paket Terlebih Dahulu ---' })"
        x-init="initVariantChoices($el, $watch)"
        x-modelable="value"
        x-model="state.variantId"
        x-on:change="loadTimeSlots()"
      >
        <option value="">--- Pilih Paket Terlebih Dahulu ---</option>
      </select>
      <template x-if="state.errors['package_variant_id']">
        <p
          class="mt-1.5 text-xs text-red-600"
          x-text="Array.isArray(state.errors['package_variant_id']) ? state.errors['package_variant_id'][0] : state.errors['package_variant_id']"
        ></p>
      </template>
    </div>
    {{-- pilih varian end --}}

    {{-- pilih background start --}}
    <div>
      <label
        for="background_id"
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
      >
        Background <span class="text-red-500">*</span>
      </label>
      <select
        id="background_id"
        name="background_id"
        x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Background ---' })"
        x-modelable="value"
        x-model="state.backgroundId"
      >
        <option value="">--- Pilih Background ---</option>
        @foreach ($backgrounds as $bg)
          <option value="{{ $bg->id }}">{{ $bg->name }}</option>
        @endforeach
      </select>
      <template x-if="state.errors['background_id']">
        <p
          class="mt-1.5 text-xs text-red-600"
          x-text="Array.isArray(state.errors['background_id']) ? state.errors['background_id'][0] : state.errors['background_id']"
        ></p>
      </template>
    </div>
    {{-- pilih background end --}}

  </div>
</div>
{{-- package selection card end --}}
