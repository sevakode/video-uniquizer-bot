<?php
require 'vendor/autoload.php';
require 'config.php';
require 'functions.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);;
use Telegram\Bot\Api;

$telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
$telegram->setTimeOut(600);

$updateItem = $telegram->getWebhookUpdates();
$configFile = 'config.json';
$message = $updateItem->getMessage();


if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
    $flipStatus = $config['flip'];
} else {
    $flipStatus = filter_var(env('FLIP_STATUS'), FILTER_VALIDATE_BOOLEAN);
}
if ($message->getText() == '/flip') {

    $flipStatus = !$flipStatus;
    $config['flip'] = $flipStatus;
    file_put_contents($configFile, json_encode($config));

    $statusText = $flipStatus ? 'включено' : 'выключено';
    $telegram->sendMessage([
        'chat_id' => $message->getChat()->getId(),
        'text' => 'Отзеркаливание ' . $statusText,
    ]);

    return;
}

$video = $message->getVideo();
//    $photo = $message->getPhoto();
if ($video !== null) {

    process_video($telegram, $message, $flipStatus);
    return;
}

else {
    $telegram->sendMessage([
        'chat_id' => $message->getChat()->getId(),
        'text' => 'Пожалуйста, отправьте мне видео для уникализации.'
    ]);
    return;
}