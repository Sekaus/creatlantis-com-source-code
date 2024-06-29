<?php
    session_start();
    
    include_once '../mysql_functions/store_data.php';
    
    // submit and remove items to and from folder
    if(isset($_GET['folder']) && isset($_GET['mode'])) {
        if(isset($_POST['data'])) {
            foreach (json_decode($_POST['data']) as $itemURL) {
                if($_GET['mode'] == "add_item")
                    addFolderItem($_SESSION['uuid'], $_GET['folder'], $itemURL);
                else if($_GET['mode'] == "remove_item")
                    removeFolderItem($_SESSION['uuid'], $_GET['folder'], $itemURL);
            }
        }
        else if($_GET['mode'] == "remove_folder") {
            removeFolder($_SESSION['uuid'], $_GET['folder']);
        }
    }
?>