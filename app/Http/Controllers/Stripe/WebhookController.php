<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

final class WebhookController extends Controller
{
    public function __construct(
        private readonly StripeWebhookService $webhookService,
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $event = $this->webhookService->verifyAndParse(
                payload:   $request->getContent(),
                signature: $request->header('Stripe-Signature') ?? '',
            );
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook parse failed', ['error' => $e->getMessage()]);
            return response('Bad request', 400);
        }

        $this->webhookService->dispatch($event);

        return response('OK', 200);
    }
}
