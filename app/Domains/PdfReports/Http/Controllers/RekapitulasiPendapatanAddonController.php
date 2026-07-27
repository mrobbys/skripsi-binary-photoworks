<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\PdfReports\Http\Requests\DateRangeReportRequest;
use App\Http\Controllers\Controller;
use App\Domains\PdfReports\Traits\HasPdfMetadata;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class RekapitulasiPendapatanAddonController extends Controller
{
    use HasPdfMetadata;

    public function __invoke(DateRangeReportRequest $request)
    {
        $startDate = Carbon::parse($request->validated('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->validated('end_date'))->endOfDay();

        /**
         * Ambil pendapatan add-on dari booking yang sudah LUNAS atau SELESAI
         * berdasarkan booking_date dalam rentang tanggal filter.
         *
         * Menggunakan booking_date (bukan payments.pay_date) untuk menghindari
         * double-count akibat 1 booking bisa memiliki 2 baris payment SETTLEMENT (DP + Pelunasan).
         *
         * DP_PAID dikecualikan: add-on hanya dihitung dari booking yang 100% lunas
         * agar pendapatan yang dilaporkan benar-benar sudah diterima.
         */
        $addonRows = Booking::whereIn('status', [
            BookingStatus::SUCCESS,
            BookingStatus::DONE,
        ])
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->join('addon_booking', 'bookings.id', '=', 'addon_booking.booking_id')
            ->join('addons', 'addon_booking.addon_id', '=', 'addons.id')
            ->select('addons.id', 'addons.name')
            ->selectRaw('SUM(addon_booking.quantity) as total_qty')
            ->selectRaw('SUM(addon_booking.price_at_purchase * addon_booking.quantity) as total_pendapatan')
            ->groupBy('addons.id', 'addons.name')
            ->orderByDesc('total_pendapatan')
            ->get();

        $rows = $addonRows->map(function ($addon, $index) {
            return new Fluent([
                'no' => $index + 1,
                'name' => $addon->name,
                'total_qty' => number_format($addon->total_qty, 0, ',', '.') . ' Item',
                'total_pendapatan' => Formatter::rupiah($addon->total_pendapatan ?? 0),
                'raw_total' => $addon->total_pendapatan ?? 0,
                'raw_qty' => $addon->total_qty ?? 0,
            ]);
        });

        $grandTotalQty = number_format($rows->sum('raw_qty'), 0, ',', '.') . ' Item';
        $grandTotalPendapatan = Formatter::rupiah($rows->sum('raw_total'));
        $filterText = Formatter::dateId($startDate) . ' s/d ' . Formatter::dateId($endDate);

        return pdf()
            ->view('pdfs.rekapitulasi-pendapatan-addon', $this->pdfData(compact('rows', 'grandTotalQty', 'grandTotalPendapatan', 'filterText')))
            ->format('a4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->name("laporan-rekapitulasi-pendapatan-addon-{$startDate->format('d-m-Y')}-sd-{$endDate->format('d-m-Y')}.pdf");
    }
}
