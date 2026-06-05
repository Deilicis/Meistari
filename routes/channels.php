<?php

declare(strict_types=1);

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notifications.{userId}', function ($user, int $userId) {
    return $user->getId() === $userId;
});

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }
    if ($conversation->getSenderId() !== $user->getId()
        && $conversation->getReceiverId() !== $user->getId()) {
        return false;
    }
    return ['id' => $user->getId(), 'name' => $user->getName()];
});
