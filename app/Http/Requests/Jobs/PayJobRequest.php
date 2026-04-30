<?php

declare(strict_types=1);

namespace App\Http\Requests\Jobs;

use Illuminate\Foundation\Http\FormRequest;

class PayJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array { return []; }
}
