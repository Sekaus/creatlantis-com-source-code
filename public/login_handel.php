<?php
    ob_clean(); // clear any accidental whitespace or BOM
    header("Content-Type: application/json; charset=utf-8");
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);   // Write to PHP error log instead
    error_reporting(E_ALL);

    session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include_once("./config.php");
    include_once("./user_classes.php");
    include_once("./data_handler.php");

    header("Content-Type: application/json");

    if (!isset($_POST["email"]) || !isset($_POST["password"])) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing fields"]);
        exit;
    }

    $dh = new DataHandle($dbConfig, $s3Config);

    $login = $dh->loginAsUser($_POST["email"], $_POST["password"]);

    if ($login) {
        echo json_encode(["success" => true]);
        exit;
    } 
    else {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
        exit;
    }
?>