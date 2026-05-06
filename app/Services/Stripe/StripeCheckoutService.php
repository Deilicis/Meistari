<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\DTOs\Stripe\CreateCheckoutSessionDTO;

final class StripeCheckoutService
{
    public function __construct(
        private readonly StripeClient $stripeClient,
    ) {}

    public function createSession(CreateCheckoutSessionDTO $dto): \Stripe\Checkout\Session
    {
        return $this->stripeClient->getClient()->checkout->sessions->create([
            'mode'                 => 'payment',
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => config('services.stripe.currency'),
                    'product_data' => [
                        'name'        => $dto->jobTitle,
                        'description' => 'Maksājums par darbu Meistari.lv platformā',
                    ],
                    'unit_amount'  => (int) round($dto->amount * 100),
                ],
                'quantity'   => 1,
            ]],
            'success_url'          => $dto->successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $dto->cancelUrl,
            'metadata'             => [
                'job_request_id' => (string) $dto->jobRequestId,
                'client_id'      => (string) $dto->clientId,
                'master_id'      => (string) $dto->masterId,
            ],
            'client_reference_id'  => (string) $dto->jobRequestId,
        ]);
    }
}
