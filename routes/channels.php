<?php

declare(strict_types=1);

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    return $conversation && (
        $conversation->getSenderId() === $user->getId() ||
        $conversation->getReceiverId() === $user->getId()
    );
});
