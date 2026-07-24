<?php

namespace App\Domains\PdfReports\Http\Controllers;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Http\Controllers\Controller;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Fluent;

use function Spatie\LaravelPdf\Support\pdf;

class PendapatanTahunanController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'tahun' => ['required', 'integer', 'digits:4', 'min:2000'],
        ]);

        $tahun = (int) $request->input('tahun');

        // Ambil pembayaran SETTLEMENT pada tahun ini (berdasarkan pay_date) untuk Total Pendapatan
        $payments = Payment::where('status', PaymentStatus::SETTLEMENT)
            ->whereYear('pay_date', $tahun)
            ->get();

        $groupedPayments = $payments->groupBy(
            fn($p) => (int) ($p->pay_date?->month ?? $p->created_at->month)
        );

        // Ambil booking valid (DP_PAID, SUCCESS, DONE) pada tahun ini (berdasarkan booking_date) untuk Total Sesi Foto
        $bookings = Booking::select(['id', 'booking_date', 'status'])
            ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
            ->whereYear('booking_date', $tahun)
            ->get();

        $groupedBookings = $bookings->groupBy(
            fn($b) => (int) $b->booking_date->month
        );

        $bulanNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $rawRows = collect($bulanNames)->map(function (string $namaBulan, int $bulanNo) use ($groupedPayments, $groupedBookings) {
            $monthPayments = $groupedPayments->get($bulanNo, collect());
            $monthBookings = $groupedBookings->get($bulanNo, collect());

            $totalPendapatan = $monthPayments->sum('amount');
            $totalSesi = $monthBookings->count();

            return [
                'no' => $bulanNo,
                'bulan' => $namaBulan,
                'total_sesi' => $totalSesi,
                'total_pendapatan' => $totalPendapatan,
            ];
        })->values();

        $totalSesiSemua = $rawRows->sum('total_sesi') . ' Sesi';
        $grandTotalPendapatan = Formatter::rupiah($rawRows->sum('total_pendapatan'));

        $rows = $rawRows->map(fn($row) => new Fluent([
            'no' => $row['no'],
            'bulan' => $row['bulan'],
            'total_sesi' => $row['total_sesi'] . ' Sesi',
            'total_pendapatan' => Formatter::rupiah($row['total_pendapatan']),
        ]));

        $printedBy = Auth::user()?->name ?? 'Administrator';
        $printDate = Formatter::dateId(Carbon::now());
        $filterText = "Tahun {$tahun}";

        return pdf()
            ->view('pdfs.pendapatan-tahunan', compact(
                'rows',
                'tahun',
                'totalSesiSemua',
                'grandTotalPendapatan',
                'printedBy',
                'printDate',
                'filterText',
            ))
            ->format('a4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->name("laporan-pendapatan-tahunan-{$tahun}.pdf");
    }
}
