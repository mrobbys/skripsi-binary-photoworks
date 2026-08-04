<?php

namespace App\Domains\Frontdoor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use Illuminate\Http\JsonResponse;
use App\Domains\Frontdoor\Http\Requests\ContactUsStoreRequest;
use Illuminate\Support\Facades\Mail;

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
            Mail::to('mhmmdrobby48@gmail.com')->queue(
                new ContactMessageMail(
                    senderName: $validated['nama'],
                    senderEmail: $validated['email'],
                    mailSubject: $validated['subjek'],
                    message: $validated['pesan'],
                )
            );

            return $this->successResponse('Pesan Anda berhasil dikirim! Kami akan segera menghubungi Anda.');
        } catch (\RuntimeException $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengirim email. Silakan coba lagi.', 500);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengirim email. Silakan coba lagi.', 500);
        }
    }
}
