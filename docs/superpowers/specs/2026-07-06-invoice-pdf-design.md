# Design Specification: Invoice PDF Generation

## 1. Overview
Implement an automated PDF invoice generation feature for users after a successful booking/payment, utilizing `spatie/laravel-pdf` (v2). The invoice will be styled using Tailwind CSS (via CDN) and will follow a clean, minimalist, grayscale aesthetic as requested.

## 2. Technical Stack
- **PDF Generation:** `spatie/laravel-pdf`
- **Styling:** Tailwind CSS (loaded via CDN)
- **Data Sources:** `Payment`, `Booking`, `User`, `PackageVariant`, `Addon`.

## 3. Visual Design & Aesthetic (Taste)
- **Palette:** Strictly Black, White, and Neutral Grays (Zinc/Gray scales). No colors.
- **Typography:** Sans-serif (Inter or default Tailwind sans).
- **Layout:** 
  - **Header:** Binary Photoworks logo on the left, "INVOICE" text and metadata (Invoice #, Order ID, Date) right-aligned.
  - **Billing Info:** "Billed To:" section with the User's name, email, and phone number.
  - **Itemized Table:** Clean table with `border-b` on rows. Columns: Description, Qty, Unit Price, Amount. 
    - Row 1: The selected Package Variant.
    - Row 2+: Any Add-ons purchased.
  - **Totals:** Right-aligned subtotal and grand total.
  - **Footer:** A simple "Thank you for trusting Binary Photoworks" message.

## 4. Data Mapping
- **Invoice Number:** `Booking->booking_code`
- **Order ID (Midtrans):** `Payment->order_id` (Ditampilkan sebagai referensi)
- **Date:** `Payment->pay_date` (formatted as `d F Y`).
- **Customer:** `User->name`, `User->email`, `User->phone`.
- **Items:** 
  - Main Package: `Booking->packageVariant->name` & `Booking->packageVariant->price`.
  - Addons: Iterating through `$booking->addons` (pivot `quantity` and `price_at_purchase`).
- **Total:** `Payment->amount`.

## 5. Implementation Code

Berikut adalah kode yang siap di-copy-paste untuk implementasi PDF Invoice secara mandiri.

### 5.1. File: `app/Http/Controllers/DownloadInvoiceController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\Payment; // Sesuaikan dengan namespace model Payment lu
use Illuminate\Routing\Controllers\Controller;
use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController extends Controller
{
    public function __invoke(Payment $payment)
    {
        // Eager load relasi yang dibutuhkan agar tidak N+1 (Sesuai eloquent-best-practices)
        $payment->load([
            'booking.user', 
            'booking.packageVariant', 
            'booking.addons'
        ]);

        // Cek otorisasi simpel: hanya pemilik booking atau admin yang bisa download
        // Opsional: pakai Gate/Policy kalau ada
        if (auth()->id() !== $payment->booking->user_id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return pdf()
            ->view('pdfs.invoice', ['payment' => $payment])
            ->format('a4')
            ->name("invoice-{$payment->booking->booking_code}.pdf");
    }
}
```

### 5.2. File: `routes/web.php`

```php
use App\Http\Controllers\DownloadInvoiceController;

// Pastikan ditaruh di dalam middleware 'auth'
Route::get('/payments/{payment:uuid}/invoice', DownloadInvoiceController::class)
    ->name('payments.invoice');
```

### 5.3. File: `resources/views/pdfs/invoice.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $payment->booking->booking_code }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-zinc-900 font-sans antialiased">
    <div class="max-w-4xl mx-auto p-10">
        <!-- Header Section -->
        <div class="flex justify-between items-start border-b border-zinc-200 pb-8 mb-8">
            <div>
                <!-- Pakai public_path agar puppeteer/chromium bisa baca gambarnya dari local disk -->
                <img src="{{ public_path('assets/binary-logo/binary-logo-text-black.png') }}" alt="Binary Photoworks" class="h-10 object-contain">
            </div>
            <div class="text-right">
                <h1 class="text-3xl font-bold tracking-tight text-zinc-900">INVOICE</h1>
                <div class="mt-2 text-sm text-zinc-600">
                    <p>Invoice #: <span class="font-medium text-zinc-900">{{ $payment->booking->booking_code }}</span></p>
                    <p>Order ID: <span class="font-medium text-zinc-900">{{ $payment->order_id }}</span></p>
                    <p>Date: <span class="font-medium text-zinc-900">{{ \Carbon\Carbon::parse($payment->pay_date ?? $payment->created_at)->format('d F Y') }}</span></p>
                    <p>Status: <span class="uppercase font-bold tracking-wider text-zinc-900">{{ $payment->status }}</span></p>
                </div>
            </div>
        </div>

        <!-- Billed To Section -->
        <div class="mb-10">
            <h2 class="text-xs font-bold tracking-widest text-zinc-400 uppercase mb-2">Billed To</h2>
            <div class="text-sm">
                <p class="font-bold text-zinc-900 text-base">{{ $payment->booking->user->name }}</p>
                <p class="text-zinc-600">{{ $payment->booking->user->email }}</p>
                <p class="text-zinc-600">{{ $payment->booking->user->phone ?? '-' }}</p>
            </div>
        </div>

        <!-- Table Section -->
        <div class="mb-10">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b-2 border-zinc-900 text-zinc-900">
                        <th class="py-3 pr-4 font-bold uppercase tracking-wider text-xs">Description</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-center">Qty</th>
                        <th class="py-3 px-4 font-bold uppercase tracking-wider text-xs text-right">Unit Price</th>
                        <th class="py-3 pl-4 font-bold uppercase tracking-wider text-xs text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    <!-- Main Package -->
                    <tr>
                        <td class="py-4 pr-4">
                            <p class="font-medium text-zinc-900">{{ $payment->booking->packageVariant->name ?? 'Package' }}</p>
                            <p class="text-xs text-zinc-500 mt-1">Package Booking</p>
                        </td>
                        <td class="py-4 px-4 text-center">1</td>
                        <td class="py-4 px-4 text-right">Rp {{ number_format($payment->booking->packageVariant->price ?? 0, 0, ',', '.') }}</td>
                        <td class="py-4 pl-4 text-right text-zinc-900">Rp {{ number_format($payment->booking->packageVariant->price ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    
                    <!-- Addons (if any) -->
                    @foreach($payment->booking->addons as $addon)
                    <tr>
                        <td class="py-4 pr-4">
                            <p class="font-medium text-zinc-900">{{ $addon->name }}</p>
                            <p class="text-xs text-zinc-500 mt-1">Addon</p>
                        </td>
                        <td class="py-4 px-4 text-center">{{ $addon->pivot->quantity }}</td>
                        <td class="py-4 px-4 text-right">Rp {{ number_format($addon->pivot->price_at_purchase, 0, ',', '.') }}</td>
                        <td class="py-4 pl-4 text-right text-zinc-900">Rp {{ number_format($addon->pivot->quantity * $addon->pivot->price_at_purchase, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="flex justify-end mb-16 text-sm">
            <div class="w-1/2 md:w-1/3">
                <div class="flex justify-between py-2 border-b border-zinc-200">
                    <span class="text-zinc-600">Subtotal</span>
                    <span class="text-zinc-900 font-medium">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-zinc-900">
                    <span class="text-zinc-600">Tax / Admin Fee</span>
                    <span class="text-zinc-900 font-medium">Rp 0</span>
                </div>
                <div class="flex justify-between py-3 font-bold text-base text-zinc-900">
                    <span>Grand Total</span>
                    <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="border-t border-zinc-200 pt-8 text-center text-xs text-zinc-500">
            <p>Thank you for trusting Binary Photoworks.</p>
            <p class="mt-1">If you have any questions concerning this invoice, please contact our support.</p>
        </div>
    </div>
</body>
</html>
```
