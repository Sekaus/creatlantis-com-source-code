<?php
    session_start();
    
    include_once '../mysql_functions/store_data.php';
    include_once '../mysql_functions/login_handler.php';
    
    /* Perform Action Based on the Watch Command Value */
    
    if(isset($_POST['command']) && isset($_POST['uuid']) && isset($_POST['watcher_uuid']) && checkLoginOwnership($_SESSION['uuid'], $_SESSION['password'])) {
        $command = $_POST['command'];
        $uuid = $_POST['uuid'];
        $watcherUuid = $_POST['watcher_uuid'];
        
        switch($command) {
            case 'add_watcher':
                addWatcher($uuid, $watcherUuid);
                break;
            case 'remove_watcher':
                removeWatcher($uuid, $watcherUuid);
                break;
        }
    }
?>