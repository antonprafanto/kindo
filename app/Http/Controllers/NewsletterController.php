<?php

namespace App\Http\Controllers;

use App\Services\NewsletterService;
use App\Services\TurnstileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterController extends Controller
{
    public function __construct(private NewsletterService $newsletter) {}

    public function show()
    {
        return view('newsletter');
    }

    public function subscribe(Request $request, TurnstileService $turnstile)
    {
        try {
            if ($request->filled('website')) {
                return $this->successResponse($request, 'Cek email kamu untuk konfirmasi langganan newsletter.');
            }

            if ($turnstile->isConfigured() && ! $turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
                return $this->errorResponse(
                    $request,
                    'Verifikasi keamanan gagal. Silakan coba lagi.',
                    'turnstile'
                );
            }

            $key = 'newsletter:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                return $this->errorResponse($request, "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            }
            RateLimiter::hit($key, 600);

            $validated = $request->validate([
                'email' => 'required|email|max:200',
            ]);

            $result = $this->newsletter->subscribe($validated['email'], $request->ip());

            $message = match ($result) {
                'already_active' => 'Email kamu sudah terdaftar di newsletter kami.',
                'resent', 'pending' => 'Cek email kamu dan klik link konfirmasi untuk menyelesaikan langganan.',
                default => 'Cek email kamu untuk konfirmasi langganan newsletter.',
            };

            return $this->successResponse($request, $message);
        } catch (\Throwable $e) {
            Log::error('Newsletter subscribe failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse($request, 'Gagal mendaftar newsletter. Silakan coba lagi beberapa saat.');
        }
    }

    public function confirm(string $token)
    {
        $subscriber = $this->newsletter->confirm($token);

        if (!$subscriber) {
            return view('newsletter-status', [
                'title'   => 'Link Tidak Valid',
                'message' => 'Link konfirmasi tidak valid atau sudah kedaluwarsa. Silakan daftar ulang.',
                'success' => false,
            ]);
        }

        return view('newsletter-status', [
            'title'   => 'Langganan Aktif!',
            'message' => 'Terima kasih! Kamu akan menerima notifikasi saat ada artikel baru di Koding Indonesia.',
            'success' => true,
        ]);
    }

    public function unsubscribe(string $token)
    {
        $subscriber = $this->newsletter->unsubscribe($token);

        if (!$subscriber) {
            return view('newsletter-status', [
                'title'   => 'Link Tidak Valid',
                'message' => 'Link berhenti langganan tidak valid atau sudah digunakan.',
                'success' => false,
            ]);
        }

        return view('newsletter-status', [
            'title'   => 'Berhenti Berlangganan',
            'message' => 'Kamu sudah berhenti berlangganan newsletter. Kami harap bisa bertemu lagi!',
            'success' => true,
        ]);
    }

    private function successResponse(Request $request, string $message)
    {
        if ($request->expectsJson() || $request->boolean('ajax')) {
            return response()->json(['message' => $message]);
        }

        return back()->with('newsletter_success', $message);
    }

    private function errorResponse(Request $request, string $message, string $field = 'email')
    {
        if ($request->expectsJson() || $request->boolean('ajax')) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }
}
