<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Enums\Role\RoleNameEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class SwitchRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_role' => ['required', 'string', new Enum(RoleNameEnum::class)],
        ];
    }
}
