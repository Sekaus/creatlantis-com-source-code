<?php
    include_once("./config.php");

    session_start();
    
    include_once("./user_classes.php");
    include_once("./data_handler.php");

    if(isset($_POST["command"])) {
        if(isset($_SESSION["user_data"])) {
            $user = unserialize($_SESSION["user_data"]);
            if(isset($_POST["theme"]) && $_POST["command"] == "swap_theme")
                $user->setColorTheme($_POST["theme"]);
            else if($_POST["command"] == "update_profile") {
                // Added simple validation to ensure fields exist
                $user->setName($_POST["name"] ?? "");
                $user->setEmail($_POST["email"] ?? "");
                $user->setLand($_POST["land"] ?? "");
                $user->setDateOfBirth($_POST["dateOfBirth"] ?? "");
                $user->setGender($_POST["gender"] ?? "");
                $user->setUsername($_POST["username"] ?? "");
                $user->setTagline($_POST["tagline"] ?? "");
                $user->setHobbies($_POST["hobbies"] ?? "");
                $user->setBiography($_POST["bio"] ?? "");
            }
            else
                return ['success' => false, 'error' => 'Invalid command. ' . $_POST["command"]];

            if(isset($_SESSION["user_login"])) {
                $login = unserialize($_SESSION["user_login"]);

                $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
                $dh->updateUserInfo($user, $login);
            }

            $_SESSION["user_data"] = serialize($user);

            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Missing parameters.'];
    }
?>