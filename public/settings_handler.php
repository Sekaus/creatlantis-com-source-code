<?php
    include_once("./config.php");

    session_start();
    
    include_once("./user_classes.php");
    include_once("./data_handler.php");

    if(isset($_POST["command"])) {
        if(isset($_POST["theme"]) && isset($_SESSION["user_data"])) {
            $user = unserialize($_SESSION["user_data"]);
            if($_POST["command"] == "swap_theme")
                $user->setColorTheme($_POST["theme"]);
            else
                return ['success' => false, 'error' => 'Invalid command. ' . $_POST["command"]];

            if(isset($_SESSION["user_login"])) {
                $login = unserialize($_SESSION["user_login"]);

                $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);
                $dh->updateUserInfo($user, $login);
            }

            $_SESSION["user_data"] = serialize($user);

            return ['success' => true];
        }
    }
?>