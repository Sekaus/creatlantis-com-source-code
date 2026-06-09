<?php
    include_once("./config.php");
    include_once("./data_handler.php");

    $search_type = isset($_POST["type"]) ? $_POST["type"] : "all";
    $search_text = isset($_POST["text"]) ? $_POST["text"] : "";
    $search_order = isset($_POST["order"]) ? $_POST["order"] : "newest";

    $type = FileType::all;
    $order = FileLoadOrder::newest;

    if ($search_type === "all")
        $type = FileType::all;
    else if ($search_type === "image")
        $type = FileType::image;
    else if ($search_type === "journal")
        $type = FileType::journal;
    else if ($search_type === "profile")
        $type = FileType::profile;

    if ($search_order === "newest")
        $order = FileLoadOrder::newest;
    else if ($search_order === "oldest")
        $order = FileLoadOrder::oldest;

    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

    // Prepare response data as a PHP associative array

    $response = [
        "status" => "success", 
        "search" => [
            "type" => $type->name,
            "text" => $search_text,
            "order" => $order->name
        ]];

    if ($type != FileType::profile)
        $response["files"] = $dh->loadAllFiles($type, $search_text, $order, 10, 0);
    else
        $response["profiles"] = $dh->loadProfiles($search_text, $order, 10, 0);

    // Correctly encode as JSON and echo
    header('Content-Type: application/json');
    echo json_encode($response);
?>