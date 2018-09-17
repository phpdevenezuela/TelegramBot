<?php

require_once __DIR__.'/vendor/autoload.php';

$update = json_decode(file_get_contents('php://input'), true);

// Event Listeners & Triggers
require_once __DIR__.'/bot_listeners.php';
require_once __DIR__.'/bot_triggers.php';
