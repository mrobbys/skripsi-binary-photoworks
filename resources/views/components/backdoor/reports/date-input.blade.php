@props(['name', 'label' => 'TANGGAL', 'placeholder' => 'DD-MM-YYYY', 'value' => null])

<div>
  <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-stone-700">
    {{ $label }}
  </label>
  <div class="relative">
    <input
      type="text"
      name="{{ $name }}"
      value="{{ $value }}"
      placeholder="{{ $placeholder }}"
      {{ $attributes->merge(['class' => 'js-flatpickr w-full border border-stone-300 bg-stone-50 px-3.5 py-2.5 text-sm text-stone-900 placeholder-stone-400 transition focus:border-stone-700 focus:bg-white focus:outline-none focus:ring-0 cursor-pointer']) }}
    />
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-stone-500">
      <i
        class="ri-calendar-line text-lg"
        aria-hidden="true"
      ></i>
    </div>
  </div>
</div>
