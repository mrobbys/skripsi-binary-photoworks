@php
  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => 'Manajemen Pemesanan', 'url' => route('backdoor.booking-management.index')],
      ['label' => 'Tambah Booking Manual', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Tambah Booking Manual"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/booking-management/create/Create"
>

  <x-slot:content>
    <div
      x-data="Create"
      x-init="init(
          {{ Js::from($packages) }},
          {{ Js::from($addons) }}
      )"
      x-cloak
      class="pb-16"
    >

      {{-- header start --}}
      <div class="space-y-6 pb-6">
        <a
          href="{{ route('backdoor.booking-management.index') }}"
          class="inline-flex cursor-pointer items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-stone-500 transition hover:text-stone-950 md:text-xs"
        >
          <i
            class="ri-arrow-left-line"
            aria-hidden="true"
          ></i>
          <span>Kembali Ke Manajemen Pemesanan</span>
        </a>
        <x-backdoor.shared.page-header title="Tambah Booking Manual" />
      </div>
      {{-- header end --}}

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- section left start --}}
        <div class="space-y-5 lg:col-span-2">

          {{-- section user start --}}
          <div class="border border-stone-300">
            <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Informasi Klien</h2>
            </div>
            <div class="p-5">
              <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                Pilih Klien <span class="text-red-500">*</span>
              </label>
              <select
                x-data="choices({
                    placeholder: true,
                    placeholderValue: '--- Pilih Klien ---'
                })"
                x-modelable="value"
                x-model="state.userId"
              >
                @foreach ($users as $user)
                  <option value="{{ $user->id }}">
                    {{ $user->name }} - {{ $user->phone }} - {{ $user->email }}
                  </option>
                @endforeach
              </select>

              <template x-if="state.errors['user_id']">
                <p
                  class="mt-1.5 text-xs text-red-600"
                  x-text="state.errors['user_id'][0]"
                ></p>
              </template>
            </div>
          </div>
          {{-- section user end --}}

          {{-- secition paket start --}}
          <div class="border border-stone-300">
            <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Detail Paket</h2>
            </div>
            <div class="space-y-4 p-5">

              {{-- pilih paket start --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                  Paket <span class="text-red-500">*</span>
                </label>
                <select
                  x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Paket ---' })"
                  x-modelable="value"
                  x-model="state.packageId"
                >
                  <option value=""></option>
                  @foreach ($packages as $pkg)
                    <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                  @endforeach
                </select>
                <template x-if="state.errors['package_id']">
                  <p
                    class="mt-1.5 text-xs text-red-600"
                    x-text="state.errors['package_id'][0]"
                  ></p>
                </template>
              </div>
              {{-- pilih paket end --}}

              {{-- pilih varian start --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                  Varian <span class="text-red-500">*</span>
                </label>
                <select
                  x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Varian ---' })"
                  x-init="$watch('state.variants', (variants) => {
                      if (!$el._choices) return;
                      const mapped = variants.map(v => ({
                          value: String(v.id),
                          label: v.name + ' (' + v.duration + ' menit) — Rp ' + Number(v.price).toLocaleString('id-ID')
                      }));
                      $el._choices.clearStore();
                      $el._choices.setChoices(mapped, 'value', 'label', true);
                  })"
                  x-modelable="value"
                  x-model="state.variantId"
                  x-on:change="loadTimeSlots()"
                >
                  <option value=""></option>
                </select>
                <template x-if="state.errors['package_variant_id']">
                  <p
                    class="mt-1.5 text-xs text-red-600"
                    x-text="state.errors['package_variant_id'][0]"
                  ></p>
                </template>
              </div>
              {{-- pilih varian end --}}

              {{-- pilih background start --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                  Background <span class="text-red-500">*</span>
                </label>
                <select
                  x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Background ---' })"
                  x-modelable="value"
                  x-model="state.backgroundId"
                >
                  @foreach ($backgrounds as $bg)
                    <option value="{{ $bg->id }}">{{ $bg->name }}</option>
                  @endforeach
                </select>
                <template x-if="state.errors['background_id']">
                  <p
                    class="mt-1.5 text-xs text-red-600"
                    x-text="state.errors['background_id'][0]"
                  ></p>
                </template>
              </div>
              {{-- pilih background end --}}

            </div>
          </div>
          {{-- secition paket start --}}

          {{-- section jadwal sesi start --}}
          <div class="border border-stone-300">
            <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Jadwal Sesi</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">

              {{-- tanggal start --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                  Tanggal Sesi <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  x-model="state.bookingDate"
                  x-init="flatpickr($el, {
                      dateFormat: 'Y-m-d',
                      minDate: 'today',
                      onChange: (selectedDates, dateStr) => {
                          state.bookingDate = dateStr;
                          loadTimeSlots();
                      }
                  })"
                  placeholder="--- Pilih tanggal ---"
                  readonly
                  class="w-full cursor-pointer border border-stone-300 bg-stone-50 px-3 py-3 text-sm text-stone-900 placeholder-stone-400 focus:border-stone-500 focus:outline-none focus:ring-1 focus:ring-stone-500"
                >
                <template x-if="state.errors['booking_date']">
                  <p
                    class="mt-1.5 text-xs text-red-600"
                    x-text="state.errors['booking_date'][0]"
                  ></p>
                </template>
              </div>
              {{-- tanggal end --}}

              {{-- slot waktu start --}}
              <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-stone-700">
                  Slot Waktu <span class="text-red-500">*</span>
                </label>
                <select
                  x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Slot Waktu ---' })"
                  x-init="$watch('state.isTimeSlotsLoading', (isLoading) => {
                      if (!$el._choices) return;
                      if (isLoading) {
                          $el._choices.clearStore();
                          $el._choices.setChoices([{ value: '', label: 'Sedang memuat slot...', disabled: true, selected: true, placeholder: true }], 'value', 'label', true);
                      }
                  });
                  $watch('state.timeSlots', (slots) => {
                      if (!$el._choices) return;
                      const mapped = slots.map(s => ({
                          value: s.start_time,
                          label: s.start_time + ' – ' + s.end_time
                      }));
                      $el._choices.clearStore();
                      $el._choices.setChoices([
                          { value: '', label: '--- Pilih Slot Waktu ---', disabled: true, selected: true, placeholder: true },
                          ...mapped
                      ], 'value', 'label', true);
                  })"
                  x-modelable="value"
                  x-model="state.startTime"
                >
                </select>
                <template x-if="state.errors['start_time']">
                  <p
                    class="mt-1.5 text-xs text-red-600"
                    x-text="state.errors['start_time'][0]"
                  ></p>
                </template>
              </div>
              {{-- slot waktu end --}}

            </div>
          </div>
          {{-- section jadwal sesi end --}}

          {{-- section layanan tambahan start --}}
          <div class="border border-stone-300">
            <div class="flex items-center justify-between border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Layanan Tambahan <span
                  class="text-xs font-normal text-stone-600"
                >(Opsional)</span></h2>
              <button
                type="button"
                x-on:click="addAddonRow()"
                class="inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-stone-600 transition-colors hover:text-stone-900"
              >
                <i class="ri-add-line"></i> Tambah Layanan
              </button>
            </div>
            <div class="space-y-3 p-5">

              <template x-if="state.addons.length === 0">
                <div class="space-y-1 text-xs italic text-stone-400">
                  <p>Belum ada layanan tambahan.</p>
                  <p>Klik "+ Tambah Layanan" untuk menambahkan.</p>
                </div>
              </template>

              <template
                x-for="(item, index) in state.addons"
                :key="item.id"
              >
                <div class="flex items-center gap-3">
                  <div class="flex-1">
                    <select
                      x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
                      x-init="const updateChoices = (newAddons) => {
                          if (!$el._choices) return;
                          const mapped = state.allAddons
                              .filter(a => {
                                  // Hilangkan dari list jika sudah dipilih di baris lain
                                  const isSelectedByOther = newAddons.some(row =>
                                      String(row.addon_id) === String(a.id) &&
                                      String(row.id) !== String(item.id)
                                  );
                                  return !isSelectedByOther;
                              })
                              .map(a => ({
                                  value: String(a.id),
                                  label: a.name + ' — Rp ' + Number(a.price).toLocaleString('id-ID'),
                                  selected: String(item.addon_id) === String(a.id)
                              }));
                          $el._choices.clearStore();
                          $el._choices.setChoices([{ value: '', label: '--- Pilih Layanan ---', placeholder: true }, ...mapped], 'value', 'label', true);
                      };
                      // Jalankan pertama kali saat di-mount
                      setTimeout(() => updateChoices(state.addons), 50);
                      // Pantau perubahan berikutnya
                      $watch('state.addons', (newAddons) => updateChoices(newAddons), { deep: true });"
                      x-modelable="value"
                      x-model="item.addon_id"
                      x-on:change="onAddonChange(item)"
                    >
                      <option value=""></option>
                    </select>
                  </div>

                  {{-- input qty start --}}
                  <div class="w-20">
                    <input
                      type="number"
                      x-model.number="item.quantity"
                      x-bind:disabled="isQtyDisabled(item)"
                      min="1"
                      class="w-full border border-stone-300 bg-stone-50 px-3 py-2.5 text-center text-sm text-stone-900 focus:border-stone-500 focus:outline-none disabled:bg-stone-100 disabled:text-stone-400"
                    >
                  </div>
                  {{-- input qty end --}}

                  {{-- hapus addon start --}}
                  <button
                    type="button"
                    x-on:click="removeAddonRow(index)"
                    class="p-1 text-stone-300 transition-colors hover:text-red-500"
                  >
                    <i class="ri-delete-bin-line text-lg"></i>
                  </button>
                  {{-- hapus addon end --}}

                </div>
              </template>

            </div>
          </div>
          {{-- section layanan tambahan end --}}

        </div>
        {{-- section left end --}}

        {{-- section right start --}}
        <div class="space-y-4">

          {{-- status booking start --}}
          <div class="border border-stone-300">
            <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Status Awal Booking</h2>
            </div>
            <div class="p-5">
              <select
                x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Status ---' })"
                x-modelable="value"
                x-model="state.bookingStatus"
              >
                <option value="DP Terbayar">DP Terbayar - Sudah Bayar 60%</option>
                <option value="Lunas">LUNAS - Bayar Full</option>
              </select>
              <template x-if="state.errors['status']">
                <p
                  class="mt-1.5 text-xs text-red-600"
                  x-text="state.errors['status'][0]"
                ></p>
              </template>
            </div>
          </div>
          {{-- status booking end --}}

          {{-- ringkasan harga start --}}
          <div class="border border-stone-300">
            <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
              <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Ringkasan Pesanan</h2>
            </div>
            <div class="space-y-3 p-5">
              <div class="flex items-center justify-between pb-3 text-sm">
                <span class="font-semibold text-stone-600">Total Biaya Layanan</span>
                <span
                  class="font-bold text-stone-900"
                  x-text="'Rp ' + state.totalPrice.toLocaleString('id-ID')"
                ></span>
              </div>

              <!-- Hitungan berdasarkan Status -->
              <template x-if="state.bookingStatus === 'DP Terbayar'">
                <div class="space-y-2">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-500">DP Terbayar (60%)</span>
                    <span
                      class="font-bold text-emerald-600"
                      x-text="'- Rp ' + (state.totalPrice * 0.6).toLocaleString('id-ID')"
                    ></span>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-stone-900">Sisa Pelunasan (40%)</span>
                    <span
                      class="font-bold text-rose-600"
                      x-text="'Rp ' + (state.totalPrice * 0.4).toLocaleString('id-ID')"
                    ></span>
                  </div>
                </div>
              </template>

              <template x-if="state.bookingStatus === 'Lunas'">
                <div class="flex items-center justify-between text-sm">
                  <span class="font-semibold text-stone-900">Sisa Pelunasan</span>
                  <span class="font-bold text-emerald-600">Rp 0 (Lunas)</span>
                </div>
              </template>
            </div>
          </div>
          {{-- ringkasan harga end --}}

          {{-- kirim notif wa start --}}
          <div class="border border-stone-300 p-5">
            <label class="flex cursor-pointer items-start gap-3">
              <input
                type="checkbox"
                x-model="state.sendWaNotification"
                class="mt-0.5 h-4 w-4 cursor-pointer border-stone-300 text-stone-800 focus:ring-stone-500"
              >
              <div class="flex flex-col">
                <span class="text-sm font-semibold text-stone-900">Kirim Notifikasi WhatsApp</span>
                <span class="text-xs text-stone-500">Kirim rincian pesanan ke Klien.</span>
              </div>
            </label>
          </div>
          {{-- kirim notif wa end --}}

          {{-- btn simpan start --}}
          <x-shared.button
            type="button"
            x-on:click="submit()"
            x-bind:disabled="isSubmitDisabled()"
            class="w-full bg-stone-800 text-stone-50 hover:bg-stone-900"
          >
            <span x-text="state.isLoading ? 'Menyimpan...' : 'Simpan Pesanan'"></span>
          </x-shared.button>
          {{-- btn simpan end --}}

          {{-- btn batal start --}}
          <x-shared.button
            as="a"
            href="{{ route('backdoor.booking-management.index') }}"
            class="w-full border border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100 hover:text-stone-900"
          >
            Batal
          </x-shared.button>
          {{-- btn batal end --}}

        </div>
        {{-- section right end --}}

      </div>

    </div>
  </x-slot:content>

</x-layouts.backdoor.index>
