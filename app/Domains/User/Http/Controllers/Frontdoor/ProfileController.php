<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Domains\User\DTOs\ChangePasswordData;
use App\Domains\User\DTOs\ProfileData;
use App\Domains\User\Http\Requests\ChangePasswordRequest;
use App\Domains\User\Http\Requests\UpdateProfileRequest;
use App\Domains\User\Services\ProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    /**
     * Tampilkan halaman Profil
     */
    public function index(): View
    {
        return view('frontdoor.dashboard.profile');
    }

    /**
     * Update data diri user yang sedang login.
     * @param UpdateProfileRequest $request
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $this->profileService->updateProfile(
                userId: Auth::id(),
                data: ProfileData::from($request),
            );

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diperbarui.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }

    /**
     * Ganti password user yang sedang login.
     * @param ChangePasswordRequest $request
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->changePassword(
                userId: Auth::id(),
                data: ChangePasswordData::from($request),
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }
}