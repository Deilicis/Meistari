<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Helpers\ValidationRuleHelper;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->getId();

        return [
            User::NAME => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::STRING, ValidationRuleHelper::MAX_255],
            User::EMAIL => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::EMAIL, ValidationRuleHelper::MAX_255, Rule::unique(User::TABLE, User::EMAIL)->ignore($userId)],
            Profile::IS_VERIFIED => [ValidationRuleHelper::NULLABLE, ValidationRuleHelper::BOOLEAN],
        ];
    }
}
