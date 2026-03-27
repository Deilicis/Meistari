<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceApplication;

use App\DataTransferObjects\ServiceApplication\SaveServiceApplicationData;
use App\Helpers\ValidationRuleHelper as Rules;
use App\Models\Service;
use App\Models\ServiceApplication;
use Illuminate\Foundation\Http\FormRequest;

class SaveServiceApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ServiceApplication::SERVICE_ID => [
                Rules::REQUIRED,
                Rules::INTEGER,
                Rules::EXISTS . ':' . Service::TABLE . ',' . Service::ID,
            ],
            ServiceApplication::MESSAGE => [
                Rules::REQUIRED,
                Rules::STRING,
                Rules::MIN . ':10',
                Rules::MAX . ':2000',
            ],
            ServiceApplication::BUDGET_OFFER => [
                Rules::NULLABLE,
                Rules::NUMERIC,
                Rules::MIN . ':0',
                Rules::MAX . ':99999999',
            ],
        ];
    }

    public function toDTO(): SaveServiceApplicationData
    {
        $dto = new SaveServiceApplicationData();

        $dto->serviceId = (int) $this->validated(ServiceApplication::SERVICE_ID);
        $dto->userId = $this->user()->id;
        $dto->message = $this->validated(ServiceApplication::MESSAGE);

        $budgetOffer = $this->validated(ServiceApplication::BUDGET_OFFER);
        $dto->budgetOffer = $budgetOffer !== null ? (float) $budgetOffer : null;

        return $dto;
    }
}
