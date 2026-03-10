<?php

declare(strict_types=1);

namespace App\DataTransferObjects\ServiceApplication;

use App\Models\ServiceApplication;

class SaveServiceApplicationData
{
    public int $serviceId;
    public int $userId;
    public string $message;
    public ?float $budgetOffer = null;

    public function toArray(): array
    {
        return [
            ServiceApplication::SERVICE_ID   => $this->serviceId,
            ServiceApplication::USER_ID      => $this->userId,
            ServiceApplication::MESSAGE      => $this->message,
            ServiceApplication::BUDGET_OFFER => $this->budgetOffer,
        ];
    }
}
