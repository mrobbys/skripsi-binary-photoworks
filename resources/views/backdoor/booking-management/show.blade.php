@php
  if (request()->routeIs('backdoor.session-schedule.*')) {
      $backUrl = route('backdoor.session-schedule.list');
      $backLabel = 'Jadwal Sesi';
  } else {
      $backUrl = route('backdoor.booking-management.index');
      $backLabel = 'Manajemen Pemesanan';
  }

  $breadcrumbs = [
      ['label' => 'Dashboard', 'url' => route('backdoor.dashboard.index')],
      ['label' => $backLabel, 'url' => $backUrl],
      ['label' => 'Detail Booking', 'url' => ''],
  ];
@endphp

<x-layouts.backdoor.index
  title="Detail Booking — {{ $booking->booking_code }}"
  :breadcrumbs="$breadcrumbs"
  jsModule="backdoor/booking-management/show/Show"
>

  <x-slot:content>
    <div
      x-data="Show"
      x-init="mount({{ $booking->id }}, '{{ $booking->booking_code }}', {{ Js::from($availableAddons) }})"
      x-cloak
      class="pb-16"
    >

      {{-- skeleton loading start --}}
      <div x-show="state.isPageLoading">
        <x-skeleton.backdoor-booking-detail />
      </div>
      {{-- skeleton loading end --}}

      <div
        x-show="!state.isPageLoading"
        x-cloak
      >

        {{-- header start --}}
        <div class="flex items-start justify-between">
          <div class="space-y-6 pb-6">
            <a
              href="{{ $backUrl }}"
              class="inline-flex cursor-pointer items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-stone-500 transition hover:text-stone-950 md:text-xs"
            >
              <i
                class="ri-arrow-left-line"
                aria-hidden="true"
              ></i>
              <span>Kembali Ke {{ $backLabel }}</span>
            </a>
            <div class="flex flex-col gap-2">
              <div class="flex flex-wrap items-center gap-4">
                <h1
                  class="text-3xl font-bold tracking-tight text-stone-900"
                  x-text="state.bookingCode"
                ></h1>
                <x-shared.badge
                  alpine="state.booking?.status === 'Lunas' || state.booking?.status === 'Selesai'"
                  variant="lime"
                  size="sm"
                  x-text="state.booking?.status"
                />
                <x-shared.badge
                  alpine="state.booking?.status === 'DP Terbayar'"
                  variant="secondary"
                  size="sm"
                  x-text="state.booking?.status"
                />
                <x-shared.badge
                  alpine="state.booking?.status === 'Menunggu'"
                  variant="warning"
                  size="sm"
                  x-text="state.booking?.status"
                />
                <x-shared.badge
                  alpine="state.booking?.status === 'Batal'"
                  variant="danger"
                  size="sm"
                  x-text="state.booking?.status"
                />
              </div>
              <div class="flex items-center gap-2 text-sm font-medium text-stone-500">
                <span>Detail Booking</span>
                <span class="text-stone-300">&bull;</span>
                <span
                  x-text="state.booking?.created_at ? 'Dibuat pada ' + new Date(state.booking.created_at).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}).replace(/\./g, ':') + ' WITA' : '...'"
                ></span>
              </div>
            </div>
          </div>
        </div>
        {{-- header end --}}

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div class="space-y-5 lg:col-span-2">

            {{-- informasi klien start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Informasi Klien</h2>
              </div>
              <div class="grid grid-cols-1 gap-4 p-5 text-sm md:grid-cols-2">
                <div>
                  <p class="mb-1 text-xs text-stone-500">Nama Lengkap</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="state.booking?.user?.name || '-'"
                  ></p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Nomor WhatsApp</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="state.booking?.user?.phone || '-'"
                  ></p>
                </div>
                <div class="md:col-span-2">
                  <p class="mb-1 text-xs text-stone-500">Email</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="state.booking?.user?.email || '-'"
                  ></p>
                </div>
              </div>
            </div>
            {{-- informasi klien end --}}

            {{-- detail sesi pemotretan start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Detail Sesi Pemotretan</h2>
              </div>
              <div class="grid grid-cols-1 gap-6 p-5 text-sm md:grid-cols-2">
                <div>
                  <p class="mb-1 text-xs text-stone-500">Pilihan Paket</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="(state.booking?.package_variant?.package?.name || '') + ' — ' + (state.booking?.package_variant?.name || '')"
                  ></p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Background</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="state.booking?.background?.name || '-'"
                  ></p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Jadwal Sesi</p>
                  <p
                    class="font-medium text-stone-900"
                    x-text="state.booking?.booking_date ? new Date(state.booking?.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-'"
                  ></p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Waktu</p>
                  <p class="font-medium text-stone-900"><span x-text="state.booking?.start_time"></span> - <span
                      x-text="state.booking?.end_time"
                    ></span> WITA</p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Sumber Booking</p>
                  <p
                    class="font-medium capitalize text-stone-900"
                    x-text="state.booking?.source || '-'"
                  ></p>
                </div>
                <div>
                  <p class="mb-1 text-xs text-stone-500">Jumlah Reschedule</p>
                  <p class="font-medium text-stone-900">
                    <span x-text="state.booking?.reschedule_count || 0"></span> Kali
                  </p>
                </div>
                <div class="mt-2 border-t border-stone-300 pt-4 md:col-span-2">
                  <p class="mb-1 text-xs text-stone-500">Catatan Tambahan</p>
                  <p
                    class="italic text-stone-700"
                    x-text="state.booking?.notes || 'Tidak ada catatan.'"
                  ></p>
                </div>
              </div>
            </div>
            {{-- detail sesi pemotretan end --}}

            {{-- addons start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Layanan Tambahan</h2>
              </div>

              <template x-if="!state.booking?.addons?.length">
                <p class="px-5 py-4 text-xs italic text-stone-400">Tidak ada layanan tambahan.</p>
              </template>

              <template x-if="state.booking?.addons?.length">
                <table class="w-full text-sm">
                  <thead class="border-b border-stone-300">
                    <tr>
                      <th class="px-5 py-2 text-left">Layanan</th>
                      <th class="px-5 py-2 text-center">Qty</th>
                      <th class="px-5 py-2 text-right">Harga Satuan</th>
                      <th class="px-5 py-2 text-right">Subtotal</th>
                      <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
                        <th class="px-5 py-2 text-right w-16">Aksi</th>
                      </template>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-stone-300">
                    <template
                      x-for="addon in state.booking?.addons"
                      :key="addon.id"
                    >
                      <tr>
                        <td
                          class="px-5 py-3 text-stone-700"
                          x-text="addon.name"
                        ></td>
                        <td
                          class="px-5 py-3 text-center text-stone-600"
                          x-text="addon.pivot.quantity"
                        ></td>
                        <td
                          class="px-5 py-3 text-right text-stone-600"
                          x-text="formatRupiah(addon.pivot.price_at_purchase)"
                        ></td>
                        <td
                          class="px-5 py-3 text-right font-semibold text-stone-800"
                          x-text="formatRupiah(addon.pivot.quantity * addon.pivot.price_at_purchase)"
                        ></td>
                        <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
                          <td class="px-5 py-3 text-right">
                            <button 
                              type="button"
                              x-on:click="removeAddon(addon.id)" 
                              x-bind:disabled="state.upsell.isLoading"
                              class="text-red-500 hover:text-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                              title="Hapus Layanan">
                              <i class="ri-delete-bin-line"></i>
                            </button>
                          </td>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </template>

              {{-- Inline Form Upsell --}}
              <template x-if="state.booking?.status !== 'Batal' && state.booking?.status !== 'Selesai'">
                <div class="border-t border-stone-300 bg-stone-100 px-5 py-4">
                  <div class="mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-700">Tambahkan Add-on Baru</h3>
                    <p class="text-xs text-stone-500">Pilih item add-on yang ingin dimasukkan ke dalam pesanan ini.</p>
                  </div>
                  <div class="flex gap-3">
                    <div class="flex-1">
                      <select
                        x-data="choices({ placeholder: true, placeholderValue: '--- Pilih Layanan ---' })"
                        x-init="const updateChoices = () => {
                            if (!$el._choices) return;
                            const currentAddons = state.booking?.addons || [];
                            const mapped = state.allAddons
                                .filter(a => {
                                    if (!a.has_quantity) {
                                        return !currentAddons.some(booked => String(booked.id) === String(a.id));
                                    }
                                    return true;
                                })
                                .map(a => ({
                                    value: String(a.id),
                                    label: a.name + ' — ' + formatRupiah(a.price)
                                }));
                            $el._choices.clearStore();
                            $el._choices.setChoices([{ value: '', label: '--- Pilih Layanan ---', placeholder: true }, ...mapped], 'value', 'label', true);
                        };
                        setTimeout(() => updateChoices(), 50);
                        $watch('state.allAddons', updateChoices);
                        $watch('state.booking?.addons', updateChoices, { deep: true });"
                        x-modelable="value"
                        x-model="state.upsell.addonId"
                        x-on:change="onUpsellAddonChange()"
                      >
                      </select>
                    </div>
                    <div class="w-20">
                      <input
                        type="number"
                        x-model.number="state.upsell.quantity"
                        x-bind:disabled="state.upsell.addonId && !state.allAddons.find(a => a.id == state.upsell.addonId)?.has_quantity"
                        min="1"
                        class="w-full border border-stone-300 px-3 py-2.5 text-center text-sm focus:border-stone-500 focus:outline-none disabled:bg-stone-100 disabled:text-stone-400"
                      >
                    </div>
                    <button
                      type="button"
                      x-on:click="submitUpsell()"
                      x-bind:disabled="state.upsell.isLoading || !state.upsell.addonId"
                      class="bg-stone-800 px-4 py-2 text-xs font-bold text-stone-50"
                    >
                      <span x-text="state.upsell.isLoading ? 'Menambahkan...' : '+ Tambah'"></span>
                    </button>
                  </div>
                </div>
              </template>
            </div>
            {{-- addons end --}}
          </div>

          <div class="space-y-4">
            {{-- ringkasan keuangan start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Ringkasan Pembayaran</h2>
              </div>
              <div class="space-y-3 p-5">
                <div class="flex justify-between text-sm">
                  <span class="text-stone-500">Total Tagihan</span>
                  <span
                    class="font-semibold"
                    x-text="formatRupiah(state.booking?.total_price || 0)"
                  ></span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-stone-500">Total Terbayar</span>
                  <span
                    class="font-semibold text-emerald-700"
                    x-text="formatRupiah(state.booking?.payments?.filter(p => p.status === 'Settlement').reduce((sum, p) => sum + p.amount, 0) || 0)"
                  ></span>
                </div>
                <div class="flex justify-between border-t border-stone-300 pt-3 text-sm">
                  <span class="font-semibold text-stone-700">Sisa Tagihan</span>
                  <span
                    class="font-bold"
                    x-bind:class="(state.booking?.total_price || 0) - (state.booking?.payments?.filter(p => p
                        .status === 'Settlement').reduce((sum, p) => sum + p.amount, 0) || 0) <= 0 ? 'text-stone-500' :
                        'text-red-600'"
                    x-text="formatRupiah(Math.max(0, (state.booking?.total_price || 0) - (state.booking?.payments?.filter(p => p.status === 'Settlement').reduce((sum, p) => sum + p.amount, 0) || 0)))"
                  ></span>
                </div>

                <div class="mt-4 border-t border-stone-300 pt-4" x-show="state.bookingCode">
                  <a
                    :href="`/payments/${state.bookingCode}/receipt`"
                    target="_blank"
                    class="flex w-full items-center justify-center gap-2 border border-stone-300 bg-stone-50 px-4 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-100 hover:text-stone-900"
                  >
                    <i class="ri-printer-line text-lg" aria-hidden="true"></i>
                    <span>Cetak / Lihat Kuitansi</span>
                  </a>
                </div>
              </div>
            </div>
            {{-- ringkasan keuangan end --}}

            {{-- riwayat pembayaran start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Riwayat Transaksi</h2>
              </div>
              <div class="divide-y divide-stone-300">
                <template x-if="!state.booking?.payments?.length">
                  <p class="p-5 text-xs italic text-stone-400">Belum ada transaksi.</p>
                </template>
                <template
                  x-for="payment in state.booking?.payments"
                  :key="payment.id"
                >
                  <div class="flex items-start justify-between p-5 text-sm">
                    <div class="space-y-1">
                      <p
                        class="text-xs font-bold uppercase tracking-wider"
                        x-text="payment.payment_purpose"
                      ></p>
                      <p
                        class="font-mono text-xs"
                        x-text="'#' + payment.order_id"
                      ></p>
                      <p
                        class="text-xs capitalize"
                        x-text="(payment.payment_type || 'Unknown') + (payment.payment_type === 'manual' ? ' (Admin)' : ' (Midtrans)')"
                      ></p>
                      <template x-if="payment.pay_date">
                        <p
                          class="text-xs"
                          x-text="'Dibayar: ' + new Date(payment.pay_date).toLocaleString('id-ID', {day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'}) + ' WITA'"
                        ></p>
                      </template>
                    </div>
                    <div class="text-right">
                      <p
                        class="font-bold"
                        :class="payment.status === 'Settlement' ? 'text-emerald-700' : ''"
                        x-text="formatRupiah(payment.amount)"
                      ></p>
                      <div class="mt-1">
                        <x-shared.badge
                          alpine="payment.status === 'Settlement'"
                          variant="success"
                          x-text="payment.status"
                        />
                        <x-shared.badge
                          alpine="payment.status !== 'Settlement'"
                          variant="secondary"
                          x-text="payment.status"
                        />
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
            {{-- riwayat pembayaran end --}}

            {{-- tombol lunas start --}}
            <template x-if="
              (state.booking?.status === 'DP Terbayar' || state.booking?.status === 'Lunas') &&
              (Number(state.booking?.total_price || 0) > (state.booking?.payments?.filter(p => p.status === 'Settlement').reduce((sum, p) => sum + Number(p.amount), 0) || 0))
            ">
              <x-shared.button
                type="button"
                x-on:click="settle()"
                class="w-full bg-lime-600 text-stone-50 hover:bg-lime-700"
              >
                <x-slot:iconLeft>
                  <i class="ri-checkbox-circle-line"></i>
                </x-slot:iconLeft>
                Tandai Lunas
              </x-shared.button>
            </template>
            {{-- tombol lunas end --}}

            {{-- form gdrive start --}}
            <div class="border border-stone-300">
              <div class="border-b border-stone-300 bg-stone-100 px-5 py-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-stone-700">Link Google Drive</h2>
              </div>
              <form
                class="space-y-4 p-5"
                x-on:submit.prevent="submitGdrive()"
              >
                <div>
                  <input
                    type="url"
                    x-model="state.gdriveLink"
                    placeholder="https://drive.google.com/..."
                    x-bind:disabled="state.isGdriveLoading || ['Menunggu', 'DP Terbayar', 'Batal'].includes(state.booking?.status)"
                    class="w-full border border-stone-300 bg-transparent px-3 py-2 text-sm disabled:cursor-not-allowed disabled:opacity-50"
                  />
                  <small
                    class="block text-red-600"
                    x-text="state.gdriveErrors.gdriveLink"
                    x-show="state.gdriveErrors.gdriveLink"
                  >
                  </small>
                </div>
                <label class="flex cursor-pointer items-start gap-2">
                  <input
                    type="checkbox"
                    x-model="state.sendWaNotificationGdrive"
                    class="mt-1 h-4 w-4 cursor-pointer border-stone-300 text-stone-800 focus:ring-stone-500"
                  >
                  <div class="flex flex-col">
                    <span class="text-sm font-semibold text-stone-900">Kirim Notifikasi WhatsApp</span>
                    <span class="text-xs text-stone-500">Kirim Link Google Drive ke Klien.</span>
                  </div>
                </label>
                <x-shared.button
                  type="submit"
                  x-bind:disabled="state.isGdriveLoading || !state.gdriveLink || (state.booking?.gdrive_link === state.gdriveLink) || [
                      'Menunggu', 'DP Terbayar', 'Batal'
                  ].includes(state
                      .booking
                      ?.status)"
                  class="w-full bg-stone-800 text-stone-50"
                >
                  <span x-text="state.isGdriveLoading ? 'Menyimpan Link...' : 'Simpan Link'">
                  </span>
                </x-shared.button>
              </form>
            </div>
            {{-- form gdrive end --}}
          </div>
        </div>

      </div>

    </div>

  </x-slot:content>
</x-layouts.backdoor.index>
