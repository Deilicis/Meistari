<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            Message::CONVERSATION_ID => Conversation::factory(),
            Message::SENDER_ID => User::factory(),
            Message::BODY => fake()->realText(random_int(20, 150)),
            Message::READ_AT => fake()->boolean(70) ? fake()->dateTimeBetween('-1 month', 'now') : null, 
        ];
    }
}