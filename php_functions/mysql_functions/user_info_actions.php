<?php
    include_once 'store_data.php';
    
    /* Perform Action Based on the User Info Command Value */
    
    if(isset($_POST['user_info_command'])) {
        // update the old profile info to the new
        if($_POST['user_info_command'] == 'update_user_profile_info') {
            $response = updateUserProfileInfo($_POST['new_username'], $_POST['new_tagline'], $_POST['new_bio'], $_POST['new_date_of_birth'], $_POST['new_gender']);
            alertResponse($response);
        }
        else {
            $response = updateUserLoginInfo($_POST['old_password'], $_POST['new_email'], $_POST['new_password']);
            alertResponse($response);
        }
    }
?>