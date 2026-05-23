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
        Log::info('WEBHOOK_CONTROLLER_HIT', [
            'method' => $request->method(),
            'has_signature' => $request->hasHeader('Stripe-Signature'),
            'content_length' => strlen($request->getContent()),
        ]);

        try {
            $event = $this->webhookService->verifyAndParse(
                payload: $request->getContent(),
                signature: $request->header('Stripe-Signature') ?? '',
            );
            Log::info('WEBHOOK_VERIFIED', ['type' => $event->type]);
        } catch (SignatureVerificationException $e) {
            Log::warning('WEBHOOK_SIG_FAIL', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook parse failed', ['error' => $e->getMessage()]);
            return response('Bad request', 400);
        }

        $this->webhookService->dispatch($event);

        return response('OK', 200);
    }
}