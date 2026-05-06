<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Services\Jobs\JobLifecycleService;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

final class StripeWebhookService
{
    public function __construct(
        private readonly JobLifecycleService $jobLifecycleService,
    ) {}

    /**
     * @throws SignatureVerificationException
     */
    public function verifyAndParse(string $payload, string $signature): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret'),
        );
    }

    public function dispatch(\Stripe\Event $event): void
    {
        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event),
            default                      => null,
        };
    }

    private function handleCheckoutCompleted(\Stripe\Event $event): void
    {
        /** @var \Stripe\Checkout\Session $session */
        $session = $event->data->object;

        $jobRequestId = (int) ($session->metadata->job_request_id ?? 0);

        if ($jobRequestId === 0) {
            Log::warning('Stripe checkout.session.completed missing job_request_id', ['session_id' => $session->id]);
            return;
        }

        $amountReceived = ($session->amount_total ?? 0) / 100;

        $this->jobLifecycleService->confirmPaymentReceived(
            jobRequestId: $jobRequestId,
            stripeSessionId: $session->id,
            amountReceived: $amountReceived,
        );
    }

    private function handleCheckoutExpired(\Stripe\Event $event): void
    {
        Log::info('Stripe checkout.session.expired', ['session_id' => $event->data->object->id]);
    }
}
