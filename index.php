<?php
// Подключаем автозагрузчик Composer и файл с настройками
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Telegram\Bot\Api;
use Telegram\Bot\FileUpload\InputFile;

function create_video($speed, $contrast, $noise, $saturation, $brightness, $rotation,$blur_radius,$flipStatus)
{
    $ffmpeg = FFMpeg::create();

    $video = $ffmpeg->open('input.mp4');
    // добавляем изображение
    $imagePath = 'image.png';
    $image = $ffmpeg->open($imagePath);

    $videoWidth = $video->getStreams()->videos()->first()->get('width');
    $videoHeight = $video->getStreams()->videos()->first()->get('height');

    $imageWidth = $image->getStreams()->videos()->first()->get('width');
    $imageHeight = $image->getStreams()->videos()->first()->get('height');

    // случайное смещение по X в пределах +/- 20% от середины видео
    $offsetX = round($videoWidth * (rand(-20, 20) / 100));
    $overlayX = ($videoWidth - $imageWidth) / 2 + $offsetX;

    $overlayY = $videoHeight - $imageHeight - 20; // отступ 20 пикселей от нижней границы

    $video->filters()->watermark($imagePath, array(
        'position' => 'absolute',
        'x' => $overlayX,
        'y' => $overlayY,
    ));


    $filters = '';
    $filters .= "boxblur=enable='between(t,0,0.5)':luma_radius={$blur_radius},";
    $filters .= "rotate={$rotation}*PI/180,";
    if ($flipStatus) {
        $filters .= "hflip,";
    }
    $filters .= "setpts={$speed}*PTS,";
    $filters .= "eq=contrast={$contrast}:brightness={$brightness},";
    $filters .= "noise=alls={$noise}:allf=t,";
    $filters .= "eq=saturation={$saturation}";
    $video->filters()->custom($filters);

    $format = new X264();
    $video->save($format, 'output.mp4');

// Список телефонов
    $phones = [
        ['Make' => 'Apple', 'Model' => 'iPhone 12'],
        ['Make' => 'Samsung', 'Model' => 'Galaxy S21'],
        ['Make' => 'Google', 'Model' => 'Pixel 5'],
        ['Make' => 'OnePlus', 'Model' => 'OnePlus 8T'],
        // Добавьте дополнительные телефоны в список
    ];

// Выбираем случайный телефон
    $randomPhone = $phones[array_rand($phones)];
// Случайные значения
    $audio_bitrate = rand(96000, 256000);
    $audio_channels = rand(1, 2);
    $audio_sample_rate = rand(22050, 48000);
    $video_frame_rate = rand(24, 60);
    $image_width = rand(480, 1920);
    $image_height = rand(270, 1080);

// Изменение метаданных EXIF
    $creationDate = randomDate('2020-01-01', '2023-04-24');
    $modifyDate = randomDate($creationDate, '2023-04-24');
    $make = $randomPhone['Make'];
    $model = $randomPhone['Model'];

// Выполняем команду exiftool для изменения метаданных
    $exiftoolCommand = "exiftool -CreateDate='{$creationDate}' -ModifyDate='{$modifyDate}' -Make='{$make}' -Model='{$model}' -api quicktimeutc=1 -AudioBitrate='{$audio_bitrate}' -AudioChannels='{$audio_channels}' -AudioSampleRate='{$audio_sample_rate}' -VideoFrameRate='{$video_frame_rate}' -ImageWidth='{$image_width}' -ImageHeight='{$image_height}' output.mp4";

//    $exiftoolCommand = "exiftool -CreateDate='{$creationDate}' -ModifyDate='{$modifyDate}' -Make='{$make}' -Model='{$model}' -api quicktimeutc=1 output.mp4";
    exec($exiftoolCommand, $output);

    unlink('output.mp4_original');
    rename('output.mp4', 'VIDEO.mp4');
}
function randomDate($start_date, $end_date)
{
    $min = strtotime($start_date);
    $max = strtotime($end_date);

    $rand_time = rand($min, $max);

    return date('Y:m:d H:i:s', $rand_time);
}
function create_zip_archive($source_file, $archive_name)
{
    $zip = new ZipArchive();
    if ($zip->open($archive_name, ZipArchive::CREATE) !== true) {
        return false;
    }
    $zip->addFile($source_file, basename($source_file));
    $zip->close();
    return true;
}
function process_video($telegram, $message, $flipStatus)
{
    $video = $message->getVideo();
    $file = $telegram->getFile(['file_id' => $video->getFileId()]);
    $filePath = $file->getFilePath();
    $downloadLink = "https://api.telegram.org/file/bot" . env('API_KEY') . "/{$filePath}";

    file_put_contents('input.mp4', fopen($downloadLink, 'r'));

//Переменные
    $blur_radius = rand(0, 50);       // Радиус размытия
    $speed = rand(80, 113) / 100;       // изменение скорости на 20%
    $contrast = rand(85, 100) / 100;    // изменение контрастности на 15%
    $noise = rand(5, 70) / 100;         // добавление шумов на 5-70%
    $saturation = rand(95, 105) / 100;  // изменение насыщенности на 5%
    $brightness = rand(-8, 8) / 100;    // изменение яркости на 8%
    $rotation = rand(-50, 50) / 100;    // поворот на 0.5 градусов влево или вправо
//Переменные

    //дефолт видео
//    create_video(1, 1, 0, 1, 0, 0);
    //минимум
//    create_video(0.95, 0.95, 0.05, 0.95, -0.05, -0.6);

    create_video($speed, $contrast, $noise, $saturation, $brightness, $rotation,$blur_radius,$flipStatus);
    //Создание видео
    $archive_name = 'unique_video.zip';
    create_zip_archive('VIDEO.mp4', $archive_name);

    $telegram->sendDocument([
        'chat_id' => $message->getChat()->getId(),
        'document' => InputFile::create($archive_name, $archive_name),
        'caption' => 'Вот ваше уникальное видео!',
        'parse_mode' => 'HTML',
    ]);

    unlink('input.mp4');
    unlink('VIDEO.mp4');
    unlink($archive_name);
}

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
