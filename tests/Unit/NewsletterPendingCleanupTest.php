<?php

namespace Tests\Unit;

use App\Models\NewsletterSubscriber;
use App\Services\NewsletterService;
use Tests\TestCase;

class NewsletterPendingCleanupTest extends TestCase
{
    public function test_resend_confirmation_rejects_non_pending(): void
    {
        $subscriber = new NewsletterSubscriber([
            'email' => 'aktif@example.com',
            'status' => 'active',
            'confirmation_token' => null,
        ]);

        $service = new NewsletterService();

        $this->assertFalse($service->resendConfirmation($subscriber));
    }

    public function test_service_exposes_pending_helpers(): void
    {
        $this->assertTrue(method_exists(NewsletterService::class, 'purgeStalePending'));
        $this->assertTrue(method_exists(NewsletterService::class, 'resendConfirmation'));
        $this->assertTrue(method_exists(NewsletterSubscriber::class, 'scopePendingOlderThan'));
    }
}
