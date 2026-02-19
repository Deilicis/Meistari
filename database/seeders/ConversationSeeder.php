<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->count() < 2) {
            return;
        }

        for ($i = 0; $i < 15; $i++) {
            /** @var User $sender */
            $sender = $users->random();
            
            /** @var User $receiver */
            $receiver = $users->where('id', '!=', $sender->getId())->random();

            $exists = Conversation::where(function ($query) use ($sender, $receiver) {
                $query->where(Conversation::SENDER_ID, $sender->getId())
                      ->where(Conversation::RECEIVER_ID, $receiver->getId());
            })->orWhere(function ($query) use ($sender, $receiver) {
                $query->where(Conversation::SENDER_ID, $receiver->getId())
                      ->where(Conversation::RECEIVER_ID, $sender->getId());
            })->exists();

            if ($exists) {
                continue;
            }

            /** @var Conversation $conversation */
            $conversation = Conversation::factory()->create([
                Conversation::SENDER_ID => $sender->getId(),
                Conversation::RECEIVER_ID => $receiver->getId(),
            ]);

            $messageCount = random_int(5, 20);
            for ($m = 0; $m < $messageCount; $m++) {
                $messageSenderId = fake()->boolean() ? $sender->getId() : $receiver->getId();

                Message::factory()->create([
                    Message::CONVERSATION_ID => $conversation->getId(),
                    Message::SENDER_ID => $messageSenderId,
                ]);
            }
        }
    }
}