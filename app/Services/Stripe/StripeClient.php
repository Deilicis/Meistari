<?php

declare(strict_types=1);

namespace App\Services\Stripe;

final class StripeClient
{
    private \Stripe\StripeClient $client;

    public function __construct()
    {
        $this->client = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    public function getClient(): \Stripe\StripeClient
    {
        return $this->client;
    }
}
