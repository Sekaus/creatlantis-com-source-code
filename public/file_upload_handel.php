<?php
    global $dbConfig;
    global $s3Config;
    global $user;
    global $login;

    include_once("./data_handler.php");
    
    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
    
    $file = null;

    $metadata = [
        'title' =>  $_POST['title'], 
        'type' => $_POST['type'], 
        'tags' => $_POST["tags"] 
    ];

    switch($_POST["post-type"]) {
        case FileType::image:
            $file = new File(FileType::image, $metadata, $_FILES["image"]);
            break;
        case FileType::journal:
            $file = new File(FileType::journal, $metadata, $_POST["body"]);
            break;
    }

    $dh->uploadFile($file, $login, $user);
?>