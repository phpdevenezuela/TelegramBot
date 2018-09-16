<?php

use App\Event;
use App\Telegram;

// Event Triggers

$config = require __DIR__.'/config.php';
$bot = new Telegram($config);

if (!is_array($update)) {
    die('Update is not set');
}

if (array_key_exists('text', $update['message'])) {
    // Event: text
    Event::trigger('text', [$update['message']['text'], $bot, $update]);
} else if (array_key_exists('sticker', $update['message'])) {
    // Event: sticker
    Event::trigger('sticker', [$update['message']['sticker'], $bot, $update]);
} else if (array_key_exists('photo', $update['message'])) {
    // Event: photo
    Event::trigger('photo', [$update['message']['photo'], $bot, $update]);
} else if (array_key_exists('new_chat_member', $update['message'])) {
    // Event: new_chat_member
    Event::trigger('new_chat_member', [$update['message']['new_chat_member'], $bot, $update]);
}      
