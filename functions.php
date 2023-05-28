<?php
require 'vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Telegram\Bot\FileUpload\InputFile;
use Intervention\Image\ImageManagerStatic as Image;

function uniq_image($path, $flipStatus)
{
    $output='output.jpg';

    // Открываем изображение
    $image = Image::make($path);

    //Переменные
    $blur_radius = rand(0, 50);       // Радиус размытия
    $contrast = rand(85, 100) / 100;    // изменение контрастности на 15%
    $brightness = rand(-8, 8) / 100;    // изменение яркости на 8%
    $saturation = rand(95, 105) / 100;  // изменение насыщенности на 5%
    //Переменные

    $image->blur($blur_radius);
    $image->contrast(($contrast - 1) * 100);
    $image->brightness($brightness * 100);
    $image->greyscale()->colorize(0, 0, $saturation * 100);

    if ($flipStatus) {
        $image->flip('h');
    }

    // Сохраняем измененное изображение
    $image->save($output);

    metaRand($output);

    return $output;
}
function uniq_video($path,$flipStatus,$imagePath=null)
{
    $output='output.mp4';
    $ffmpeg = FFMpeg::create();

    $video = $ffmpeg->open($path);
    // добавляем изображение
    if($imagePath) {
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
    }

//Переменные
//    $blur_radius = rand(0, 50);       // Радиус размытия
    $speed = rand(80, 113) / 100;       // изменение скорости на 20%
    $contrast = rand(85, 100) / 100;    // изменение контрастности на 15%
    $noise = rand(5, 70) / 100;         // добавление шумов на 5-70%
    $saturation = rand(95, 105) / 100;  // изменение насыщенности на 5%
    $brightness = rand(-8, 8) / 100;    // изменение яркости на 8%
    $rotation = rand(-50, 50) / 100;    // поворот на 0.5 градусов влево или вправо
//Переменные


    $filters = '';
//    $filters .= "boxblur=enable='between(t,0,0.5)':luma_radius={$blur_radius},";
    $filters .= "rotate={$rotation}*PI/180,";

    $filters .= "hflip,vflip,";

    if ($flipStatus) {
        $filters .= "hflip,";
    }

    $filters .= "setpts={$speed}*PTS,";
    $filters .= "eq=contrast={$contrast}:brightness={$brightness},";
    $filters .= "noise=alls={$noise}:allf=t,";
    $filters .= "eq=saturation={$saturation}";
    $video->filters()->custom($filters);

    $format = new X264();
    $format->setKiloBitrate(2000)
        ->setAudioChannels(2)
        ->setAudioKiloBitrate(128)
        ->setAdditionalParameters(['-preset', 'veryfast', '-tune', 'zerolatency']); // добавлены параметры для оптимизации
    $video->save($format, $output);
    metaRand($output);
    return $output;
}

function metaRand($path){
// Список телефонов
    $phones = [
        ['Make' => 'Apple', 'Model' => 'iPhone 12'],
        ['Make' => 'Apple', 'Model' => 'iPhone 12 Pro'],
        ['Make' => 'Apple', 'Model' => 'iPhone 13'],
        ['Make' => 'Samsung', 'Model' => 'Galaxy S21'],
        ['Make' => 'Samsung', 'Model' => 'Galaxy S21 Ultra'],
        ['Make' => 'Samsung', 'Model' => 'Galaxy Note 20'],
        ['Make' => 'Google', 'Model' => 'Pixel 5'],
        ['Make' => 'Google', 'Model' => 'Pixel 6'],
        ['Make' => 'OnePlus', 'Model' => 'OnePlus 8T'],
        ['Make' => 'OnePlus', 'Model' => 'OnePlus 9'],
        ['Make' => 'Xiaomi', 'Model' => 'Mi 11'],
        ['Make' => 'Xiaomi', 'Model' => 'Redmi Note 10'],
        ['Make' => 'Sony', 'Model' => 'Xperia 5 II'],
        ['Make' => 'Sony', 'Model' => 'Xperia 1 III'],
        ['Make' => 'Huawei', 'Model' => 'P40 Pro'],
        ['Make' => 'Huawei', 'Model' => 'Mate 40 Pro'],
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
    $exiftoolCommand = "exiftool -CreateDate='{$creationDate}' -ModifyDate='{$modifyDate}' -Make='{$make}' -Model='{$model}' -api quicktimeutc=1 -AudioBitrate='{$audio_bitrate}' -AudioChannels='{$audio_channels}' -AudioSampleRate='{$audio_sample_rate}' -VideoFrameRate='{$video_frame_rate}' -ImageWidth='{$image_width}' -ImageHeight='{$image_height}' {$path}";

//    $exiftoolCommand = "exiftool -CreateDate='{$creationDate}' -ModifyDate='{$modifyDate}' -Make='{$make}' -Model='{$model}' -api quicktimeutc=1 output.mp4";
    exec($exiftoolCommand, $output);

    unlink('output.mp4_original');
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
    $downloadLink = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN') . "/{$filePath}";
    $downloadPath='input.mp4';
    $fileContent = fopen($downloadLink, 'r');
    if ($fileContent === false) {
        $telegram->sendMessage([
            'chat_id' => $message->getChat()->getId(),
            'text' => "An error occurred: 'Failed to open file: ' . $downloadLink"
        ]);
    }
    else {
        file_put_contents($downloadPath, stream_get_contents($fileContent));
    }


    //дефолт видео
//    create_video(1, 1, 0, 1, 0, 0);
    //минимум
//    create_video(0.95, 0.95, 0.05, 0.95, -0.05, -0.6);
    if(env('WATERMARK')){
        $imagePath = 'image.png';
        $video=uniq_video($downloadPath, $flipStatus, $imagePath );
    }else{
        $video=uniq_video($downloadPath, $flipStatus );
    }
    //Создание видео
    $archive_name = 'unique_video.zip';
    create_zip_archive($video, $archive_name);

    $telegram->sendDocument([
        'chat_id' => $message->getChat()->getId(),
        'document' => InputFile::create($archive_name, $archive_name),
        'caption' => 'Вот ваше уникальное видео!',
        'parse_mode' => 'HTML',
    ]);

    unlink($downloadPath);
    unlink($video);
    unlink($archive_name);
}
