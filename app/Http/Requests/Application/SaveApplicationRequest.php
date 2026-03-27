<?php

declare(strict_types=1);

namespace App\Http\Requests\Application;

use App\DataTransferObjects\Application\SaveApplicationData;
use Illuminate\Foundation\Http\FormRequest;

class SaveApplicationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'job_request_id' => ['required', 'integer', 'exists:job_requests,id'],
            'cover_letter'   => ['nullable', 'string', 'max:2000'],
            'price_offer'    => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function toDTO(): SaveApplicationData
    {
        return new SaveApplicationData(
            jobRequestId: (int) $this->input('job_request_id'),
            userId:       $this->user()->id,
            coverLetter:  $this->input('cover_letter'),
            priceOffer:   $this->input('price_offer') !== null ? (float) $this->input('price_offer') : null,
        );
    }
}
