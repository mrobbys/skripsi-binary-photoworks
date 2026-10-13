<?php

namespace App\Console\Commands;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[Signature('waitinglist:clean-expired')]
#[AsCommand(name: 'waitinglist:clean-expired')]
#[Description('Clean up expired waiting list bookings that have passed their booking date')]
class CleanExpiredWaitingListCommand extends Command
{
    public function handle(): int
    {
        $now = now();
        $todayDate = $now->toDateString();
        $currentTime = $now->format('H:i');

        $count = Booking::where('status', BookingStatus::WAITING_LIST)
            ->where(function ($query) use ($todayDate, $currentTime) {
                // kadaluarsa karena sudah beda hari
                $query->where('booking_date', '<', $todayDate)
                    // atau hari ini, tapi jam sesinya sudah lewat
                    ->orWhere(function ($subQuery) use ($todayDate, $currentTime) {
                        $subQuery->where('booking_date', '=', $todayDate)
                            ->where('start_time', '<=', $currentTime);
                    });
            })->update(['status' => BookingStatus::CANCELLED]);

        $this->info("Berhasil membersihkan {$count} data waiting list yang sudah lewat tanggal.");

        return self::SUCCESS;
    }
}
