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
                $FilteredData = [
                    'dateOfBirth' => $_POST["dateOfBirth"] ?? "",
                    'dateOfBirthVisible' => $_POST["dateOfBirthVisible"] ?? 0,
                    'gender' => $_POST["gender"] ?? "",
                    'genderVisible' => $_POST["genderVisible"] ?? 0,
                    'name' => $_POST["name"] ?? "",
                    'email' => $_POST["email"] ?? "",
                    'land' => $_POST["land"] ?? "",
                    'landVisible' => $_POST["landVisible"] ?? 0
                ];

                $user->setFilteredData(unserialize($_SESSION["user_login"]), $FilteredData);
                
                $user->setUsername($_POST["username"] ?? "");
                $user->setTagline($_POST["tagline"] ?? "");
                $user->setHobbies($_POST["hobbies"] ?? "");
                $user->setBiography($_POST["bio"] ?? "");
            }
            else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Invalid command.']);
                exit;
            }

            if(isset($_SESSION["user_login"])) {
                $login = unserialize($_SESSION["user_login"]);

                $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
                $dh->updateUserInfo($user, $login);
            }

            $_SESSION["user_data"] = serialize($user);

            // Clear any previous output buffers to ensure only JSON is sent
            if (ob_get_length()) ob_clean(); 

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        return ['success' => false, 'error' => 'Missing parameters.'];
    }
?>