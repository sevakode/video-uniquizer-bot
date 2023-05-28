<?php
require 'vendor/autoload.php';
require 'config.php';
require 'functions.php';
use Telegram\Bot\Api;

$telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
$telegram->setTimeOut(600);

$update = $telegram->getWebhookUpdates();

$message = $update->getMessage();
$configFile = 'config.json';
var_dump();
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
$photo = $message->getPhoto();
if ($video !== null) {
    process_video($telegram, $message, $flipStatus);
    return;

}elseif($photo !== null) {
    process_image($telegram, $message, $flipStatus);
    return;
}
else {
    $telegram->sendMessage([
        'chat_id' => $message->getChat()->getId(),
        'text' => 'Пожалуйста, отправьте мне видео или фото для уникализации.'
    ]);
    return;
}
