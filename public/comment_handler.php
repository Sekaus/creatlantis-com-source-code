<?php
    include_once("./config.php");
    include_once("./user_classes.php");
    include_once("./data_handler.php");

    $dh_read = new DataHandle($dbConfig, $s3Config);

    if(isset($_POST["stack_uuid"]) && isset($_POST["stack_command"])) {
        $stackUUID = $_POST["stack_uuid"];

        switch($_POST["stack_command"]) {
            case "load_replies":
                echo $dh_read->loadReplies($stackUUID);
                exit;
        }
    }
?>