<?php
require 'vendor/autoload.php';

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Telegram\Bot\FileUpload\InputFile;
use Intervention\Image\ImageManagerStatic as Image;

function uniq_video($path, $flipStatus, $imagePath = null)
{
    $bashScript = "./uniq_video.sh";

    $output = shell_exec("bash $bashScript $path $flipStatus $imagePath");

    return "output.mp4";
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
    if ($zip->addFile($source_file) !== true) {
        return false;
    }
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

    $archiveCreated = create_zip_archive($video, $archive_name);

    if (!$archiveCreated) {
        die("Could not create archive $archive_name");
    }
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
