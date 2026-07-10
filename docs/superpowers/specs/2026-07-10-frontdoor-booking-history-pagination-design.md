# Spesifikasi Kode Paginasi Booking History (Full Backend)

Berikut adalah keseluruhan kode yang diperlukan untuk mengubah sistem menjadi Full Backend Pagination. Anda cukup _copy-paste_ kode-kode di bawah ini ke file masing-masing. Kode ini sudah disesuaikan agar **persis** menggunakan komponen `frontdoor.shared.pagination` tanpa pembungkus `table`.

## 1. Backend

### `app/Domains/Booking/Repositories/BookingRepository.php`
Tambahkan fungsi `getPaginatedByUser` ke dalam repositori.

```php
    /**
     * Ambil data booking secara paginasi berdasarkan tab status
     */
    public function getPaginatedByUser(int $userId, string $tab, int $limit = 5)
    {
        $query = Booking::with(['packageVariant.package', 'background'])
            ->where('user_id', $userId);

        if ($tab === 'upcoming') {
            $query->whereIn('status', [
                BookingStatus::PENDING, 
                BookingStatus::DP_PAID, 
                BookingStatus::SUCCESS
            ]);
        } else {
            $query->whereIn('status', [
                BookingStatus::COMPLETED, 
                BookingStatus::CANCELLED
            ]);
        }

        return $query->orderBy('booking_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate($limit);
    }
```

### `app/Domains/Booking/Services/DashboardService.php`
Sesuaikan fungsi `getBookingHistory` agar menerima parameter tambahan dan mereturn object paginasi.

```php
  /**
   * Ambil booking milik user dengan paginasi
   */
  public function getBookingHistory(int $userId, string $tab, int $limit = 5)
  {
    return $this->repository->getPaginatedByUser($userId, $tab, $limit)
      ->through(fn(Booking $b) => BookingHistoryData::fromModel($b));
  }
```

### `app/Domains/User/Http/Controllers/Frontdoor/DashboardController.php`
Ubah method `appointments` agar menerima param dan menyusun response JSON sesuai format paginasi.

```php
    public function appointments(Request $request): JsonResponse
    {
        $tab = $request->query('tab', 'upcoming');
        $limit = max(1, min((int) $request->query('limit', 5), 50));
        
        $history = $this->dashboardService->getBookingHistory(Auth::id(), $tab, $limit);

        return response()->json([
            'data'         => $history->items(),
            'current_page' => $history->currentPage(),
            'last_page'    => $history->lastPage(),
            'total'        => $history->total(),
        ]);
    }
```

---

## 2. Frontend (AlpineJS)

### `resources/js/features/frontdoor/dashboard/usePagination.js`
*(Buat file baru ini)*. Ini adalah *composable* khusus untuk menangani interaksi paginasi yang akan dibaca oleh `x-frontdoor.shared.pagination`.

```javascript
export default function usePagination({ state, fetchCallback }) {
  // Inisialisasi state jika belum ada di useState.js
  if (state.currentPage === undefined) {
    state.currentPage = 1;
    state.lastPage = 1;
    state.total = 0;
  }

  const nextPage = async () => {
    if (state.currentPage < state.lastPage && !state.isLoading) {
      state.currentPage++;
      await fetchCallback();
    }
  };

  const prevPage = async () => {
    if (state.currentPage > 1 && !state.isLoading) {
      state.currentPage--;
      await fetchCallback();
    }
  };

  const goToPage = async (page) => {
    if (page === "...") return;
    const targetPage = parseInt(page, 10);
    if (targetPage >= 1 && targetPage <= state.lastPage && !state.isLoading) {
      state.currentPage = targetPage;
      await fetchCallback();
    }
  };

  const getPages = () => {
    const current = state.currentPage;
    const last = state.lastPage;
    const delta = 1;
    const range = [];
    const result = [];
    let prev;

    for (let i = 1; i <= last; i++) {
      if (i === 1 || i === last || Math.abs(i - current) <= delta) {
        range.push(i);
      }
    }

    for (const page of range) {
      if (prev !== undefined) {
        if (page - prev === 2) result.push(prev + 1);
        else if (page - prev > 2) result.push("...");
      }
      result.push(page);
      prev = page;
    }

    return result;
  };

  return {
    nextPage,
    prevPage,
    goToPage,
    getPages
  };
}
```

### `resources/js/features/frontdoor/dashboard/useAppointments.js`
Ubah logika *fetch* dan ganti penanganan *filter* dari lokal menjadi *request parameter*.

```javascript
import route from "@/lib/route";

export default function useAppointments({ state }) {
  // Ambil data booking dari backend berdasarkan tab dan halaman (page)
  const fetchAppointments = async () => {
    state.isLoading = true;

    try {
      const res = await window.axios.get(route("frontdoor.dashboard.appointments"), {
        params: {
          tab: state.activeTab,
          page: state.currentPage || 1,
        }
      });
      
      state.appointments = res.data.data;
      
      // Update meta paginasi
      state.currentPage = res.data.current_page;
      state.lastPage = res.data.last_page;
      state.total = res.data.total;
    } catch (err) {
      console.error("Gagal memuat riwayat booking:", err);
    } finally {
      state.isLoading = false;
      
      // Gulir layar perlahan ke atas saat pindah halaman
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  // Ganti tab aktif, reset ke halaman 1, lalu fetch ulang
  const switchTab = (tab) => {
    state.activeTab = tab;
    state.selectedAppointment = null;
    state.currentPage = 1;
    
    fetchAppointments();
  };

  return {
    fetchAppointments,
    switchTab,
  };
}
```

### `resources/js/features/frontdoor/dashboard/Dashboard.js`
Integrasikan *composable* baru dan *expose* fungsinya ke *root scope*.

```javascript
import useState from "./useState.js";
import useAppointments from "./useAppointments.js";
import useDetail from "./useDetail.js";
import usePayment from "./usePayment.js";
import usePagination from "./usePagination.js";

export default function Dashboard(Alpine) {
  const state = useState(Alpine);

  const { fetchAppointments, switchTab } = useAppointments({ state });
  const { showDetail, clearDetail, hasDetail } = useDetail({ state });
  const { triggerRepay } = usePayment({ state, fetchAppointments });
  
  // Init Paginasi & passing fetchAppointments sebagai callback
  const paginationControls = usePagination({ state, fetchCallback: fetchAppointments });

  const init = () => {
    fetchAppointments();
  };

  return {
    state,
    init,

    // Ekspos properti fungsi paginasi ke root level agar terbaca oleh x-frontdoor.shared.pagination
    ...paginationControls,

    // Appointments
    fetchAppointments,
    switchTab,

    // Detail
    showDetail,
    clearDetail,
    hasDetail,

    // Payment
    triggerRepay,
  };
}
```

---

## 3. Views / UI

### `resources/views/frontdoor/dashboard/jadwal.blade.php`
1. Hapus penggunakan `filteredAppointments()`. Ubah menjadi:
```html
<div x-show="!state.isLoading && state.appointments.length === 0" ...>
```
dan pada *looping* card:
```html
<template x-for="appointment in state.appointments" :key="appointment.booking_code">
```

2. Tambahkan komponen navigasi tepat di bawah div _Daftar Kartu_ (di atas bagian `Footer Policy`):
```html
  <div class="mt-8">
    <x-frontdoor.shared.pagination />
  </div>
```
