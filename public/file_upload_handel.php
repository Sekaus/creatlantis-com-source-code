<?php
    global $dbConfig;
    global $s3Config;
    global $user;

    include_once("./data_handler.php");
    
    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
    
    //if($dh->verifyOwnership())
?>