<?php
    // Start the session
    session_start();

    include_once 'store_data.php';
    
    /* Perform Action Based on the User command value */
    
    if(isset($_POST['command'])) {
        // update the old profile info to the new
        if($_POST['command'] == 'swap_theme') {
            updateUserSettings($_POST['theme']);
            $_SESSION['color_theme'] = $_POST['theme'];
        }
    }
?>