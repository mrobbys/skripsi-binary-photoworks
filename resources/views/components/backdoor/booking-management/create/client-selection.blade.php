@props(['users' => []])

{{-- client selection card start --}}
<div class="border border-stone-300">
  <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
    <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Informasi Klien</h2>
  </div>
  <div class="p-5">
    <label
      for="user_id"
      class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700"
    >
      Pilih Klien <span class="text-red-500">*</span>
    </label>
    <select
      id="user_id"
      name="user_id"
      x-data="choices({
          placeholder: true,
          placeholderValue: '--- Pilih Klien ---'
      })"
      x-modelable="value"
      x-model="state.userId"
    >
      <option value="">--- Pilih Klien ---</option>
      @foreach ($users as $user)
        <option value="{{ $user->id }}">
          {{ $user->name }} - {{ $user->phone }} - {{ $user->email }}
        </option>
      @endforeach
    </select>

    <template x-if="state.errors['user_id']">
      <p
        class="mt-1.5 text-xs text-red-600"
        x-text="Array.isArray(state.errors['user_id']) ? state.errors['user_id'][0] : state.errors['user_id']"
      ></p>
    </template>
  </div>
</div>
{{-- client selection card end --}}
