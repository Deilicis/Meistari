<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Complaint\ComplaintStatusEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resolvingStatuses = implode(',', [ComplaintStatusEnum::RESOLVED->value, ComplaintStatusEnum::DISMISSED->value]);

        return [
            Complaint::STATUS          => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::STRING,
                'in:' . implode(',', [
                    ComplaintStatusEnum::REVIEWED->value,
                    ComplaintStatusEnum::RESOLVED->value,
                    ComplaintStatusEnum::DISMISSED->value,
                ]),
            ],
            Complaint::RESOLUTION_NOTE => [
                'required_if:' . Complaint::STATUS . ',' . $resolvingStatuses,
                ValidationRuleHelper::NULLABLE,
                ValidationRuleHelper::STRING,
                ValidationRuleHelper::MAX . ':2000',
            ],
        ];
    }
}
