<?php

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\User\Models\User;
use App\Jobs\SendWhatsappNotificationJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travel;

// variabel untuk nilai default
$targetPackageName = 'Custom Photoshoot Studio';
$targetVariantName = 'Custom';
$targetCategoryCode = 'CPS';
$targetBookingDate = '2026-11-11';
$targetStartTime = '12:00';
$targetDuration = 60;
$targetPrice = 1_000_000;

// setup data master & mocking (berjalan sebelum setiap fungsi test)
beforeEach(function () use (
    $targetPackageName,
    $targetVariantName,
    $targetCategoryCode,
    $targetDuration,
    $targetPrice
) {
    // tangkap antrean wa di memori
    Queue::fake([SendWhatsappNotificationJob::class]);

    // return token dummy midtrans
    Http::fake([
        '*midtrans.com*' => Http::response([
            'token' => 'dummy-snap-token-' . uniqid(),
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/dummy'
        ], 200),
    ]);

    // create data category
    $category = Category::create([
        'category_code' => $targetCategoryCode,
        'name' => 'Custom Photoshoot',
        'is_active' => true,
    ]);

    // create data package
    $package = Package::create([
        'category_id' => $category->id,
        'name'  => $targetPackageName,
        'description'  => 'Paket photoshoot custom',
        'is_active' => true,
    ]);

    // create data package variant
    $variant = PackageVariant::create([
        'package_id'  => $package->id,
        'name'  => $targetVariantName,
        'price'  => $targetPrice,
        'duration'  => $targetDuration,
        'is_whatsapp_only' => false,
        'is_active' => true,
    ]);

    // create data background
    $background = Background::create([
        'name' => 'Putih Profil',
        'description' => 'Background studio warna putih bersih',
        'is_active' => true,
    ]);
});

// test-1 reservasi manual
test('Test-1: Klien 1 memesan jadwal normal saat slot masih kosong', function () use (
    $targetBookingDate,
    $targetStartTime,
    $targetVariantName
) {
    // buat akun Klien 1
    $clientA = User::factory()->create([
        'name' => 'Klien Pertama',
        'phone' => '081234567890'
    ]);

    // ambil data variant dan background
    $variant = PackageVariant::where('name', $targetVariantName)->first();
    $background = Background::where('name', 'Putih Profil')->first();

    $payload = [
        'package_variant_id' => $variant->id,
        'background_id' => $background->id,
        'booking_date' => $targetBookingDate,
        'start_time' => $targetStartTime,
        'payment_scheme' => PaymentScheme::LUNAS->value,
        'notes' => 'Pengujian Reservasi Normal',
    ];

    // kirim request sebagai Klien 1
    $response = actingAs($clientA)->postJson(route('frontdoor.booking.checkout'), $payload);

    // verifikasi response
    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Checkout berhasil');

    // pastikan token dummy midtrans ada
    expect($response->json('snap_token'))->not->toBeNull();

    // verifikasi database bahwa data tersimpan 1 booking dengan status pending
    assertDatabaseHas('bookings', [
        'user_id' => $clientA->id,
        'package_variant_id' => $variant->id,
        'start_time' => $targetStartTime,
        'status' => BookingStatus::PENDING->value,
    ]);

    // verifikasi database data payment terbit dengan status pending
    assertDatabaseHas('payments', [
        'status' => PaymentStatus::PENDING->value,
    ]);

    // simpan data yang dibuat ke db sqlite
    DB::commit();
});

// test 2 double booking (3 klien di slot waktu yang sama)
test('Test-2: 3 Klien memesan jadwal yang sama persis, 1 lolos dan 2 masuk Waiting List', function () use (
    $targetBookingDate,
    $targetStartTime,
    $targetVariantName
) {
    // membuat 3 akun klien berbeda
    $clientA = User::factory()->create([
        'name' => 'Klien Pertama',
        'phone' => '081111111111'
    ]);
    $clientB = User::factory()->create([
        'name' => 'Klien Kedua',
        'phone' => '082222222222'
    ]);
    $clientC = User::factory()->create([
        'name' => 'Klien Ketiga',
        'phone' => '083333333333'
    ]);

    // ambil data variant dan background
    $variant = PackageVariant::where('name', $targetVariantName)->first();
    $background = Background::where('name', 'Putih Profil')->first();

    $payload = [
        'package_variant_id' => $variant->id,
        'background_id' => $background->id,
        'booking_date' => $targetBookingDate,
        'start_time' => $targetStartTime,
        'payment_scheme' => PaymentScheme::LUNAS->value,
        'notes' => 'Pengujian bentrok 3 klien',
    ];

    // Klien A checkout terlebih dahulu (hak milik slot)
    $responseA = actingAs($clientA)->postJson(route('frontdoor.booking.checkout'), $payload);
    $responseA->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Checkout berhasil');
    expect($responseA->json('snap_token'))->not->toBeNull();

    // Klien B checkout di detik yang sama untuk slot yang sama
    $responseB = actingAs($clientB)->postJson(route('frontdoor.booking.checkout'), $payload);
    $responseB->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('is_waiting_list', true);
    expect($responseB->json('snap_token'))->toBeNull();
    expect($responseB->json('message'))->toContain('Waiting List #1');

    // Klien C checkout di detik yang sama juga untuk slot yang sama
    $responseC = actingAs($clientC)->postJson(route('frontdoor.booking.checkout'), $payload);
    $responseC->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('is_waiting_list', true);
    expect($responseC->json('snap_token'))->toBeNull();
    expect($responseC->json('message'))->toContain('Waiting List #2');

    // cek total data booking di database harus ada 3
    assertDatabaseCount('bookings', 3);

    // cek booking dengan status pending hanya boleh ada 1 (Klien A)
    expect(Booking::whereDate('booking_date', $targetBookingDate)
        ->where('start_time', $targetStartTime)
        ->where('status', BookingStatus::PENDING)
        ->count())->toBe(1);

    // cek booking dengan status wating list harus tepat ada 2 (Klien B dan C)
    expect(Booking::whereDate('booking_date', $targetBookingDate)
        ->where('start_time', $targetStartTime)
        ->where('status', BookingStatus::WAITING_LIST)
        ->count())->toBe(2);

    // pastikan stauts masing-masing user di database sudah sesuai
    assertDatabaseHas('bookings', [
        'user_id' => $clientA->id,
        'status' => BookingStatus::PENDING->value
    ]);
    assertDatabaseHas('bookings', [
        'user_id' => $clientB->id,
        'status' => BookingStatus::WAITING_LIST->value
    ]);
    assertDatabaseHas('bookings', [
        'user_id' => $clientC->id,
        'status' => BookingStatus::WAITING_LIST->value
    ]);

    // simpan data yang dibuat ke db sqlite
    DB::commit();
});

// test-3 auto release klien 1 dan auto promote ke klien 2
test('Test-3: Ketika Klien 1 melewati snap_token_expiry tanpa membayar, slot dialihkan otomatis ke Klien 2 (Waiting List #1)', function () use (
    $targetBookingDate,
    $targetStartTime,
    $targetVariantName
) {
    // membuat 3 akun klien berbeda
    $clientA = User::factory()->create([
        'name' => 'Klien Pertama',
        'phone' => '081111111111'
    ]);
    $clientB = User::factory()->create([
        'name' => 'Klien Kedua',
        'phone' => '082222222222'
    ]);
    $clientC = User::factory()->create([
        'name' => 'Klien Ketiga',
        'phone' => '083333333333'
    ]);

    // ambil data variant dan background
    $variant = PackageVariant::where('name', $targetVariantName)->first();
    $background = Background::where('name', 'Putih Profil')->first();

    $payload = [
        'package_variant_id' => $variant->id,
        'background_id' => $background->id,
        'booking_date' => $targetBookingDate,
        'start_time' => $targetStartTime,
        'payment_scheme' => PaymentScheme::LUNAS->value,
        'notes' => 'Pengujian auto release klien 1, auto promote ke klien 2',
    ];

    /**
     * Insert data
     * Klien A status = Pending
     * Klien B status = Waiting List #1
     * Klien C status = Waiting List #2
     */
    actingAs($clientA)->postJson(route('frontdoor.booking.checkout'), $payload);
    actingAs($clientB)->postJson(route('frontdoor.booking.checkout'), $payload);
    actingAs($clientC)->postJson(route('frontdoor.booking.checkout'), $payload);

    // ambil data booking dari masing" Klien
    $bookingA = Booking::where("user_id", $clientA->id)->first();
    $bookingB = Booking::where("user_id", $clientB->id)->first();
    $bookingC = Booking::where("user_id", $clientC->id)->first();

    // memastikan status booking dari masing" Klien
    expect($bookingA->status)->toBe(BookingStatus::PENDING);
    expect($bookingB->status)->toBe(BookingStatus::WAITING_LIST);
    expect($bookingC->status)->toBe(BookingStatus::WAITING_LIST);

    // simulasi waktu memajukan jam, menjadi 2 jam ke depan (melewati batas 1 jam snap_token_expiry)
    travel(2)->hours();

    // jalankan scheduler pembatalan
    Artisan::call('booking:cancel-expired');

    // verifikasi Klien A statusnya nya telah Batal
    $bookingA->refresh();
    expect($bookingA->status)->toBe(BookingStatus::CANCELLED);
    
    // jalankan scheduler promote antrean waiting list
    Artisan::call('waitinglist:promote');

    // verifikasi Klien B statusnya telah diubah menjadi Pending (hak milik slot beralih ke Klien B)
    $bookingB->refresh();
    expect($bookingB->status)->toBe(BookingStatus::PENDING);
    
    // Klien B sekarang memliki data tagihan pembayaran
    assertDatabaseHas('payments', [
        'booking_id' => $bookingB->id,
        'status' => PaymentStatus::PENDING->value
    ]);

    // pastikan Klien C status booking nya tetap Waiting List (#2 -> #1)
    $bookingC->refresh();
    expect($bookingC->status)->toBe(BookingStatus::WAITING_LIST);

    // simpan data yang dibuat ke db sqlite
    DB::commit();
});
