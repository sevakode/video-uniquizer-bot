<?php
require 'vendor/autoload.php';
require 'config.php';
require 'functions.php';
use Telegram\Bot\Api;

$telegram = new Api(env('API_KEY'));
$telegram->setTimeOut(600);

$updates = $telegram->getWebhookUpdates();
$message = $updates->getMessage();

if ($message->getText() == '/flip') {
    // получаем текущий статус из файла конфигурации
    $configFile = 'config.json';
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        $flipStatus = $config['flip'];
    } else {
        $flipStatus = (bool)env('FLIP_STATUS');
    }

    // меняем статус и сохраняем его в файле конфигурации
    $flipStatus = !$flipStatus;
    $config['flip'] = $flipStatus;
    file_put_contents($configFile, json_encode($config));

    // отправляем сообщение в чат
    $statusText = $flipStatus ? 'включено' : 'выключено';
    $telegram->sendMessage([
        'chat_id' => $message->getChat()->getId(),
        'text' => 'Отзеркаливание ' . $statusText,
    ]);

    // завершаем выполнение
    return;
}

$video = $message->getVideo();
if ($video === null) {
    $telegram->sendMessage([
        'chat_id' => $message->getChat()->getId(),
        'text' => 'Пожалуйста, отправьте мне видео для уникализации.'
    ]);
    return;
} else {
    // получаем текущий статус из файла конфигурации
    $configFile = 'config.json';
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        $flipStatus = $config['flip'];
    } else {
        $flipStatus = (bool)env('FLIP_STATUS');
    }

    process_video($telegram, $message, $flipStatus);
}
