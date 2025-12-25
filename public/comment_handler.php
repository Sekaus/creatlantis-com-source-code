<?php
    header('Content-Type: application/json');

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include_once("./config.php");
    include_once("./user_classes.php");
    include_once("./data_handler.php");

    $dh_read = new DataHandle($dbConfig, $s3Config);

    $maxKeys = 5;

    /* ---------- VALIDATE BASE INPUT ---------- */
    if (!isset($_POST["stack_command"])) {
        echo json_encode([
            "success" => false,
            "error" => "Missing stack_command"
        ]);
        exit;
    }

    $offset = isset($_POST["offset"]) ? (int)$_POST["offset"] : 0;

    /* ---------- ROUTER ---------- */
    switch ($_POST["stack_command"]) {

        case "load_post_comments":

            if (!isset($_POST["key"]) && !isset($_POST["profile_uuid"]) ) {
                echo json_encode([
                    "success" => false,
                    "error" => "Missing post key and profile_uuid"
                ]);
                exit;
            }

            echo $dh_read->loadCommentStack($_POST['key'] ?? null, $_POST["profile_uuid"] ?? null, $maxKeys, $offset);
            exit;

        case "load_replies":

            if (!isset($_POST["stack_uuid"])) {
                echo json_encode([
                    "success" => false,
                    "error" => "Missing stack_uuid"
                ]);
                exit;
            }

            echo $dh_read->loadReplies($_POST["stack_uuid"], $maxKeys, $offset);
            exit;

        default:
            echo json_encode([
                "success" => false,
                "error" => "Unknown stack_command"
            ]);
            exit;
    }
?>