<?php

declare(strict_types=1);

namespace App\Http\Requests\Chat;

use App\DataTransferObjects\Chat\SendMessageData;
use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function toDTO(int $conversationId): SendMessageData
    {
        return new SendMessageData(
            conversationId: $conversationId,
            senderId:       $this->user()->id,
            body:           $this->input('body'),
        );
    }
}
