<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use Illuminate\Http\JsonResponse;
use App\Domains\Frontdoor\Http\Requests\ContactUsStoreRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactUsController extends Controller
{
    public function index()
    {
        return view('frontdoor.contact-us.index');
    }

    public function store(ContactUsStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            Mail::to(config('studio.email'))->queue(
                new ContactMessageMail(
                    senderName: $validated['nama'],
                    senderEmail: $validated['email'],
                    mailSubject: $validated['subjek'],
                    message: $validated['pesan'],
                )
            );

            return $this->successResponse('Pesan Anda berhasil dikirim!');
        } catch (\RuntimeException $e) {
            Log::error('ContactUs Error: ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\Exception $e) {
            Log::error('ContactUs Error: ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Terjadi kesalahan saat mengirim email. Silakan coba lagi.', 500);
        }
    }
}
