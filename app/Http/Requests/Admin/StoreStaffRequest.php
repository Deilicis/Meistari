<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Role\RoleNameEnum;
use App\Helpers\ValidationRuleHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            User::NAME  => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::STRING, ValidationRuleHelper::MAX_255],
            User::EMAIL => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::EMAIL, ValidationRuleHelper::MAX_255, ValidationRuleHelper::UNIQUE . ':' . User::TABLE . ',' . User::EMAIL],
            'password'  => [ValidationRuleHelper::REQUIRED, Password::min(8)],
            'role'      => [ValidationRuleHelper::REQUIRED, ValidationRuleHelper::STRING, 'in:' . RoleNameEnum::ADMIN->value . ',' . RoleNameEnum::MODERATOR->value],
        ];
    }
}
