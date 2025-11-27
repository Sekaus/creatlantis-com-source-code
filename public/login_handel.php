<?php
    global $dbConfig;
    global $s3Config;
    global $user;
    global $login;

    session_start();

    include_once("./user_classes.php");
    include_once("./data_handler.php");

    if(isset($_POST["email"]) && isset($_POST["password"])) {
        $dh = new DataHandle($dbConfig, $s3Config);
        if(!$dh->loginAsUser($_POST["email"], $_POST["password"]))
            error_log(("The password or email has an error"));
    }
?>