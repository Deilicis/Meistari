<?php

declare(strict_types=1);

namespace App\Http\Requests\Complaint;

use App\Helpers\ValidationRuleHelper;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            Complaint::REPORTED_USER_ID => [
                ValidationRuleHelper::REQUIRED,
                ValidationRuleHelper::INTEGER,
                ValidationRuleHelper::EXISTS . ':' . User::TABLE . ',' . User::ID,
                'different:' . User::ID,
            ],
            Complaint::REPORTED_ENTITY_TYPE => [ValidationRuleHelper::NULLABLE, ValidationRuleHelper::STRING, ValidationRuleHelper::MAX_255],
            Complaint::REPORTED_ENTITY_ID => [ValidationRuleHelper::NULLABLE, ValidationRuleHelper::INTEGER],
            Complaint::REASON => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::STRING, ValidationRuleHelper::MIN . ':20', ValidationRuleHelper::MAX . ':2000'],
        ];
    }
}
