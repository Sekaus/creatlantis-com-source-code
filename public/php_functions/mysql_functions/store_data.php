<?php
    session_start();
    
    $absolute_path = dirname(__FILE__);
    include_once 'db_connect.php';
    include_once 'login_handler.php';
    include_once "$absolute_path/../../php_functions/data_filter.php";
    
    /* Post Data and Linking */
    
    // store a new link to a new post whit its tags, type and owner
    function storePostData($link, $tags, $type, $title) {
        global $mysqli;
        
        // prepare an insert statement for post_list
        $stmt = $mysqli->prepare("INSERT INTO post_list (link, tags, type, owner, title) VALUES (?, ?, ?, ?, ?)");
        // bind variables to the prepared statement as parameters to post_list
        $stmt->bind_param("sssss", $link, $tags, $type, $_SESSION['uuid'], $title);
        $stmt->execute();
        
        $stmt->close();
    }
    
    // overwrite a post's data (PS. only tags and title well be overwrited)
    function overwritePostData($link, $tags, $title) {
        global $mysqli;
        
        // prepare an update statement for post_list
        $stmt = $mysqli->prepare("UPDATE post_list SET tags=?, title=? WHERE link=?");
        // bind variables to the prepared statement as parameters to post_list
        $stmt->bind_param("sss", $tags, $title, $link);
        $stmt->execute();
        
        $stmt->close();
    }

    // remove a post form post list with its data using its link
    function removePostData($link) {
        global $mysqli;
        
        // prepare an delete statement of post_list
        $stmt = $mysqli->prepare("DELETE FROM post_list WHERE link=?");
        // bind variables to the prepared statement as parameters to post_list
        $stmt->bind_param("s", $link);
        $stmt->execute();
        
        $stmt->close();
    }
    
    /* Feedback Handler For User Posts */

    // store a single feedback made by user
    // !!! No way to see if a user login is fake or not !!!
    function storeFeedBack($post_link, $feedbackType, $value) {
        global $mysqli;
        
        // get post id
        $stmt_get = $mysqli->prepare("SELECT id, owner FROM post_list WHERE link LIKE ?");
        $post_link = "%$post_link%";
        $stmt_get->bind_param("s", $post_link);
        $stmt_get->execute();
        
        $result = $stmt_get->get_result();
        $postData = $result->fetch_row() ?? false;
        
        // check if post at post id exsist
        if($postData == false) {
            $stmt_get->close();
            exit("ERROR: invalid post_link: $post_link");
        }
        // check if the main user is the owner of the post
        else if($postData[1] == $_SESSION['uuid']) {
            $stmt_get->close();
            exit("ERROR: you can't get feedback to your own post");
        }
        
        $postID = $postData[0];
        
        $stmt_get->close();
        
        // get feedback type and prepare a statement for feedback list
        switch ($feedbackType) {
            //if it is a star rate
            case "star_rate":
                $stmt_update = $mysqli->prepare("UPDATE feedback SET star_rate=? WHERE uuid=? AND post_id=?");
                $value = (($value <= 5) ? $value : 5);
                $value = (($value >= 1) ? $value : NULL);
                $stmt_update->bind_param("isi", $value, $_SESSION['uuid'], $postID);
                $stmt_update->execute();
                $stmt_update->close();
                break;
            
            // if it is a fave
            case "fave":
                $stmt_update = $mysqli->prepare("UPDATE feedback SET fave=? WHERE uuid=? AND post_id=?");
                $stmt_update->bind_param("isi", $value, $_SESSION['uuid'], $postID);
                $stmt_update->execute();
                $stmt_update->close();
                break;
            
            // if it is a view
            case "view":
                // check if post id is already linked in the feedback table
                $stmt_check = $mysqli->prepare("SELECT post_id FROM feedback WHERE uuid=? AND post_id=?");
                $stmt_check->bind_param("si", $_SESSION['uuid'], $postID);
                $stmt_check->execute();
                
                $result = $stmt_check->get_result();
                
                // if not, then link it
                if(!(mysqli_num_rows($result) > 0)) {
                    $stmt_insert = $mysqli->prepare("INSERT INTO feedback (uuid, post_id) VALUES (?, ?)");
                    $stmt_insert->bind_param("si", $_SESSION['uuid'], $postID);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
                else
                    echo "Post at post_link $post_link is already seen by the main user";
                $stmt_check->close();
                break;
        }
    }
    
    /* Comment Handler */
    
    // add new comment to the comment_stack
    function storeComment($userUUID, $postLink = -1, $profileUUID = NULL, $comment, $replyUUID = NULL) {
        global $mysqli;
        $stack_uuid;
        $result;
        
        // get post id form post_list or -1
        $postID = -1;
        if($postLink != -1) {
            $stmt_post_id = $mysqli->prepare("SELECT id FROM post_list WHERE link LIKE ?");
            $postLink = "%$postLink%";
            $stmt_post_id->bind_param("s", $postLink);
            $stmt_post_id->execute();
            
            $result = $stmt_post_id->get_result();
            $postID = $result->fetch_row()[0] ?? -1;
            
            $stmt_post_id->close();
        }
        
        $stmt_get = $mysqli->prepare("SELECT stack_uuid FROM comment_stack WHERE stack_uuid=?");
        
        // genarate stack_uuid
        do {  
                $stack_uuid = bin2hex(random_bytes(8));
                
                $stmt_get->bind_param("s", $stack_uuid);
                $stmt_get->execute();
                
                $result = $stmt_get->get_result();
        } while (mysqli_num_rows($result) > 0);
        
        $stmt_get->close();
        
        if(checkLoginOwnership($_SESSION['uuid'], $_SESSION['password'])) {
            // add the comment and it's metadata to the comment_stack
            $stmt_insert = $mysqli->prepare("INSERT INTO comment_stack (uuid, post_id, profile_uuid, comment, stack_uuid, reply_uuid) VALUES (?, ?, ?, ?, ?, ?)");

            $comment = convertQuotesToUnicode($comment);

            $stmt_insert->bind_param("sissss", $userUUID, $postID, $profileUUID, $comment, $stack_uuid, $replyUUID);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
    }
    
    // remove a comment
    function removeComment($stackUUID) {
        global $mysqli;
        
        // check that the comment belong to the main user or the main user's profile
        $stmt_comment = $mysqli->prepare("SELECT * FROM comment_stack WHERE stack_uuid=?");
        // bind variables to the prepared statement as parameters to comment_stack
        $stmt_comment->bind_param("s", $stackUUID);
        $stmt_comment->execute();
        
        $result = $stmt_comment->get_result();
        $uuid_on_comment = $result->fetch_row() ?? -1;
        
        $stmt_comment->close();
        
        if($uuid_on_comment != -1 && ($_SESSION['uuid'] == $uuid_on_comment[0] || $_SESSION['uuid'] == $uuid_on_comment[2]) && checkLoginOwnership($_SESSION['uuid'], $_SESSION['password'])) {
            // look for any replies and replace it's reply_uuid with null
            $stmt_reply = $mysqli->prepare("UPDATE comment_stack SET reply_uuid='null' WHERE reply_uuid=?");
            // bind variables to the prepared statement as parameters to comment_stack
            $stmt_reply->bind_param("s", $uuid_on_comment[5]);
            $stmt_reply->execute();
        
            // prepare an delete statement of comment_stack
            $stmt = $mysqli->prepare("DELETE FROM comment_stack WHERE stack_uuid=?");
            // bind variables to the prepared statement as parameters to comment_stack
            $stmt->bind_param("s", $stackUUID);
            $stmt->execute();

            $stmt->close();
        }
    }
    
    // edit comment
    function editCommment($commentText, $stackUUID) {
        global $mysqli;
        
        // check that the comment belong to the main user
        $stmt_comment = $mysqli->prepare("SELECT * FROM comment_stack WHERE stack_uuid=?");
        // bind variables to the prepared statement as parameters to comment_stack
        $stmt_comment->bind_param("s", $stackUUID);
        $stmt_comment->execute();
        
        $result = $stmt_comment->get_result();
        $uuid_on_comment = $result->fetch_row() ?? -1;
        
        $stmt_comment->close();
        
        if($uuid_on_comment != -1 && ($_SESSION['uuid'] == $uuid_on_comment[0]) && checkLoginOwnership($_SESSION['uuid'], $_SESSION['password'])) {
            // prepare an update statement of comment_stack
            $stmt = $mysqli->prepare("UPDATE comment_stack SET comment=? WHERE stack_uuid=?");
            $commentText = convertQuotesToUnicode($commentText);
            // bind variables to the prepared statement as parameters to comment_stack
            $stmt->bind_param("ss",  $commentText, $stackUUID);
            $stmt->execute();

            $stmt->close();
        }
    }
    
    /* Watch Handler */
    
    // add watcher to watchers_stack
    function addWatcher($uuid, $watcherUuid) {
        global  $mysqli;
        
        // check if the watcher is already added to the stack
        $stmt_watcher = $mysqli->prepare("SELECT * FROM watchers_stack WHERE uuid=? AND watcher_uuid=?");
        // bind variables to the prepared statement as parameters to watchers_stack
        $stmt_watcher->bind_param("ss", $uuid, $watcherUuid);
        $stmt_watcher->execute();
        
        $result = $stmt_watcher->get_result();
        $last_result = $result->fetch_row() ?? -1;
        
        $stmt_watcher->close();
        
        if($last_result == -1 && $uuid != $watcherUuid) {
            // prepare an insert statement of watchers_stack
            $stmt = $mysqli->prepare("INSERT INTO watchers_stack (uuid, watcher_uuid) VALUES (?, ?)");
            // bind variables to the prepared statement as parameters to watchers_stack
            $stmt->bind_param("ss",  $uuid, $watcherUuid);
            $stmt->execute();

            $stmt->close();
        }
    }
    
    // remove watcher form watchers_stack
    function removeWatcher($uuid, $watcherUuid) {
        global $mysqli;
        
        // prepare an delete statement of watchers_stack
        $stmt = $mysqli->prepare("DELETE FROM watchers_stack WHERE uuid=? AND watcher_uuid=?");
        // bind variables to the prepared statement as parameters to watchers_stack
        $stmt->bind_param("ss",  $uuid, $watcherUuid);
        $stmt->execute();

         $stmt->close();
    }
    
    // check if the user is already watching a user
    function isTheUserWatching($uuid, $watcherUuid) {
        global $mysqli;
        
        // prepare an select statement of watchers_stack
        $stmt = $mysqli->prepare("SELECT COUNT(uuid) FROM watchers_stack WHERE uuid=? AND watcher_uuid=?");
        // bind variables to the prepared statement as parameters to watchers_stack
        $stmt->bind_param("ss",  $uuid, $watcherUuid);
        $stmt->execute();
        
        $result_count = $stmt->get_result()->fetch_row()[0];

         $stmt->close();
         
         return ($result_count > 0);
    }
    
    /* Folder Data */
    
    // add a new folder
    function addFolder($owner, $title, $description, $thumbnail) {
        global $mysqli;
        
        // check if the user is the owner of the profile that $owner pointing to
        if(checkLoginOwnership($owner, $_SESSION['password'])) {
            $folder_uuid;
            
            $stmt_get = $mysqli->prepare("SELECT folder_uuid FROM folder_stack WHERE folder_uuid=?");
            
            // generate folder_uuid
            do {  
                $folder_uuid = bin2hex(random_bytes(8));
                
                $stmt_get->bind_param("s", $folder_uuid);
                $stmt_get->execute();
                
                $result = $stmt_get->get_result();
            } while (mysqli_num_rows($result) > 0);
            
            $stmt_check = $mysqli->prepare("INSERT INTO folder_stack (owner, folder_uuid, title, description, thumbnail) VALUES (?, ?, ?, ?, ?)");
            $stmt_check->bind_param("sssss", $owner, $folder_uuid, $title, $description, $thumbnail);
            $stmt_check->execute();
        }
    }
    
    // edit a folder
    function editFolder($owner, $title, $description, $thumbnail, $folderUUID) {
        global $mysqli;
        // check if the user is the owner of the profile that $owner pointing to
        if(checkLoginOwnership($owner, $_SESSION['password'])) {
            // check if folder belong to $owner
            
            $stmt_check = $mysqli->prepare("SELECT * FROM folder_stack WHERE owner=? AND folder_uuid=?");
            $stmt_check->bind_param("ss", $owner, $folderUUID);
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
            
            if(mysqli_num_rows($result) > 0) {
                $stmt_check = $mysqli->prepare("UPDATE folder_stack SET title=?, description=?, thumbnail=? WHERE folder_uuid=?");
                $stmt_check->bind_param("ssss", $title, $description, $thumbnail, $folderUUID);
                $stmt_check->execute();
            }
        }
    }
    
    // remove a folder
    function removeFolder($owner, $folderUUID) {
        global $mysqli;
        
        // check if the user is the owner of the profile that $owner pointing to
        if(checkLoginOwnership($owner, $_SESSION['password'])) {
            // check if folder belong to $owner
            
            $stmt_check = $mysqli->prepare("SELECT * FROM folder_stack WHERE owner=? AND folder_uuid=?");
            $stmt_check->bind_param("ss", $owner, $folderUUID);
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
            
            if(mysqli_num_rows($result) > 0) {
                // first remove all items from selected folder
                $stmt_remove_items = $mysqli->prepare("DELETE FROM folder_items WHERE folder_uuid=?");
                $stmt_remove_items->bind_param("s", $folderUUID);
                $stmt_remove_items->execute();

                // then remove the selected folder
                $stmt_remove_folder = $mysqli->prepare("DELETE FROM folder_stack WHERE folder_uuid=?");
                $stmt_remove_folder->bind_param("s", $folderUUID);
                $stmt_remove_folder->execute();
            }
        }
    }

    // add item to folder
    function addFolderItem($owner, $folderUUID, $itemURL) {
        global $mysqli;
        
        // check if the user is the owner of the profile that $owner pointing to
        if(checkLoginOwnership($owner, $_SESSION['password'])) {
            // check if folder belong to $owner
            
            $stmt_check = $mysqli->prepare("SELECT * FROM folder_stack WHERE owner=? AND folder_uuid=?");
            $stmt_check->bind_param("ss", $owner, $folderUUID);
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
            
            if(mysqli_num_rows($result) > 0) {
                $stmt_insert = $mysqli->prepare("INSERT INTO folder_items (folder_uuid, post_url) VALUES (?, ?)");
                $stmt_insert->bind_param("ss", $folderUUID, $itemURL);
                $stmt_insert->execute();
            }
        }
    }
    
    // remove item from folder
    function removeFolderItem($owner, $folderUUID, $itemURL) {
        global $mysqli;
        
        // check if the user is the owner of the profile that $owner pointing to
        if(checkLoginOwnership($owner, $_SESSION['password'])) {
            // check if folder belong to $owner
            
            $stmt_check = $mysqli->prepare("SELECT * FROM folder_stack WHERE owner=? AND folder_uuid=?");
            $stmt_check->bind_param("ss", $owner, $folderUUID);
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
            
            if(mysqli_num_rows($result) > 0) {
                $stmt_insert = $mysqli->prepare("DELETE FROM folder_items WHERE folder_uuid=? AND post_url=?");
                $stmt_insert->bind_param("ss", $folderUUID, $itemURL);
                $stmt_insert->execute();
            }
        }
    }

    /* Profile Data and Login */
    
    // update a user's profile info (not email or password)
    function updateUserProfileInfo($newUsername = '', $newTagline = '', $newBIO = '', $dateOfBirthVisibility = null, $newDateOfBirth = '', $genderVisibility = null, $newGender = '', $newProfileImage = '') {
        global $mysqli;
        $response = '';
        
        // check if the new username is already taken (if it is set to a new value)
        // then set the old username to the new
        if($newUsername != '' && $newUsername != $_SESSION['username']) {
            $stmt_check = $mysqli->prepare("SELECT * FROM user_info WHERE username=?");
            $stmt_check->bind_param("s", filterUnwantedCode($newUsername));
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
            
            // check if the new username is already taken
            // if it is not, then set the old username to the new
            // else send a response
            if(mysqli_num_rows($result) > 0)
                $response = "Sorry the username is already taken...";
            else {
                $stmt_update = $mysqli->prepare("UPDATE user_info SET username=? WHERE uuid=? AND password=PASSWORD(?)");
                $stmt_update->bind_param("sss", filterUnwantedCode($newUsername), $_SESSION['uuid'], $_SESSION['password']);
                $stmt_update->execute();
                $stmt_update->close();
                
                // update username session
                $_SESSION['username'] = filterUnwantedCode($newUsername);
            }
            $stmt_check->close();
        }
        
        // set the old tagline to the new (if it is set to a new value)
        if($response == '') {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET tagline=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($newTagline), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // update tagline session
            $_SESSION['tagline'] = filterUnwantedCode($newTagline);
        }
        
        // set the old bio to the new (if it is set to a new value)
        if($response == '') {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET bio=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($newBIO), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        // set the old date of birh to the new (if it is set to a new value)
        if($newDateOfBirth != '' && $response == '') {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET date_of_birth=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($newDateOfBirth), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // update date of birth session
            $_SESSION['date_of_birth'] = filterUnwantedCode($newDateOfBirth);
        }
        
        // change visiblity on date of birth
        if($response == "") {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET date_of_birth_visible=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param('iss', $dateOfBirthVisibility, $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        // set the old gender to the new (if it is set to a new value)
        if($newGender != '' && $response == '') {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET gender=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($newGender), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
            
            // update gender session
            $_SESSION['gender'] = filterUnwantedCode($newGender);
        }
        
        // change visiblity on gender
        if($response == "") {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET gender_visible=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param('iss', $genderVisibility, $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        // set the old profile image to the new (if it is set to a new value)
        if($newProfileImage != '' && $response == '') {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET profile_image=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($newProfileImage), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
        }
        
        // if everything is going okay (no response)
        // then return a success response
        if($response == '')
            return "Success to update the profile info!";
        else
            return $response;
    }
    
    // update a user's login info
    function updateUserLoginInfo($oldPassword, $newEmail = '', $newPassword = '') {
        global $mysqli;
        $response = '';
        
        // check if the new email is already in use (if it is set to a new value)
        // then set the old email to the new
        if($newEmail != '' && $newEmail != $_SESSION['email']) {
            $stmt_check = $mysqli->prepare("SELECT * FROM user_info WHERE email=?");
            $stmt_check->bind_param("s", $newEmail);
            $stmt_check->execute();
        
            $result = $stmt_check->get_result();
        
            // check if the new email is already in use
            // if it is not, then set the old email to the new
            // else send a response
            if(mysqli_num_rows($result) > 0)
                $response = "Sorry the email is already in use...";
            else {
                // check the ownership of the profile by getting the old password and uuid
                // if user by uuid has ownership of the login, then continue (not skipping the code below)
                if (checkLoginOwnership($_SESSION['uuid'], $oldPassword)) {
                    $stmt_update = $mysqli->prepare("UPDATE user_info SET email=? WHERE uuid=? AND password=PASSWORD(?)");
                    $stmt_update->bind_param("sss", $newEmail, $_SESSION['uuid'], $oldPassword);
                    $stmt_update->execute();
                    $stmt_update->close();
                    
                    // update email session
                    $_SESSION['email'] = $newEmail;
                }
                else
                    $response = "Wrong password, no email had changed";
            }
            $stmt_check->close();
        }
        
        // set the old password to the new (if it is set to a new value)
        if($newPassword != '' && $response == '') {
            if (checkLoginOwnership($_SESSION['uuid'], $oldPassword)) {
                $stmt_update = $mysqli->prepare("UPDATE user_info SET password=PASSWORD(?) WHERE uuid=? AND password=PASSWORD(?)");
                $stmt_update->bind_param("sss", $newPassword, $_SESSION['uuid'], $oldPassword);
                $stmt_update->execute();
                $stmt_update->close();
                
                // update password session
                $_SESSION['password'] = $newPassword;
            }
            else
                $response = "Wrong password, no password had changed";
        }
        
        // if everything is going okay (no response)
        // then return a success response
        if($response == '')
            return "Success to update the login info!";
        else
            return $response;
    }
    
    function updateUserSettings($theme = "") {
        global $mysqli;
        
        // set the old color theme to the new (if it is set to a new value)
        if($theme != "") {
            $stmt_update = $mysqli->prepare("UPDATE user_info SET color_theme=? WHERE uuid=? AND password=PASSWORD(?)");
            $stmt_update->bind_param("sss", filterUnwantedCode($theme), $_SESSION['uuid'], $_SESSION['password']);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
    
    // alert update user response in JavaScript
    function alertResponse($response) {
        echo "<script>alert('$response');</script>";
    }
    
    // run this function when main user has read and accept the Terms of Service and Privacy Policy
    function HasReadAndAcceptPopup() {
        global $mysqli;
        
        // change the value of the read_and_accept_opup enum
        $mysqli->query("UPDATE user_info SET read_and_accept_opup=2 WHERE uuid='" . $_SESSION['uuid'] . "'");
    }
    
    /* Notes */
    
    // send a note to a user
    function sendNode($transmitter, $recipient, $title, $text) {
        global $mysqli;

        // check login ownership on $transmitter before sending the note
         if (checkLoginOwnership($transmitter, $_SESSION['password'])) {
             $note_uuid;
             
             // check if recipient exsist befor sending the note
             $stmt_recipient = $mysqli->prepare("SELECT uuid FROM user_info WHERE uuid=?");
             $stmt_recipient->bind_param("s", $recipient);
             $stmt_recipient->execute();
             
             $result_recipient = $stmt_recipient->get_result();
             
             if(mysqli_num_rows($result_recipient) > 0) {
                 // genarate a uuid to the note and make sure it is uniqer
                 $stmt_get = $mysqli->prepare("SELECT uuid FROM note_stack WHERE uuid=?");
                 do {
                     $note_uuid = bin2hex(random_bytes(8));
                
                     $stmt_get->bind_param("s", $note_uuid);
                     $stmt_get->execute();
                     
                     $result_uuid = $stmt_get->get_result();
                 } while (mysqli_num_rows($result_uuid) > 0);
                 
                // send note to the recipient
                $stmt_send = $mysqli->prepare("INSERT INTO note_stack (transmitter, recipient, text, title, uuid) VALUES (?, ?, ?, ?, ?)");
                $stmt_send->bind_param("sssss", $transmitter,  $recipient, $text, $title, $note_uuid);
                $stmt_send->execute();
                $stmt_send->close();
             }
             
             $stmt_recipient->close();
         }
    }
    
    // edit a note from a user
    function editNote($transmitter, $uuid, $title, $text) {
        global $mysqli;
        
        // check login ownership on $transmitter before editing the note
         if (checkLoginOwnership($transmitter, $_SESSION['password'])) {
              // try edit it the selected note
              $stmt_update = $mysqli->prepare("UPDATE note_stack SET text=?, title=? WHERE transmitter=? AND uuid=?");
              $stmt_update->bind_param("ssss", $text, $title, $transmitter, $uuid);
              $stmt_update->execute();
                  
              $stmt_update->close();
         }
    }
    
    // delete a note form a user
    function deleteNote($transmitter, $uuid) {
        global $mysqli;
        
        // check login ownership on $transmitter before deleting the note
         if (checkLoginOwnership($transmitter, $_SESSION['password'])) {
              // if the note was found, try delete it
              $stmt_delete = $mysqli->prepare("DELETE FROM note_stack WHERE uuid=? OR reply=?");
              
              $stmt_delete->bind_param("ss", $uuid, $uuid);
              $stmt_delete->execute();
                  
              $stmt_delete->close();
         }
    }
    
    // reply a note from a user
    function replyNote($transmitter, $uuid, $text) {
        global $mysqli;
        
        // check login ownership on $transmitter before replying the note
         if (checkLoginOwnership($transmitter, $_SESSION['password'])) {
             // try find the note to reply
              $stmt_get = $mysqli->prepare("SELECT uuid FROM note_stack WHERE uuid=?");
              $stmt_get->bind_param("s", $uuid);
              $stmt_get->execute();
              
              $result_uuid = $stmt_get->get_result();
              
              // if the note was found, try reply it
              if(mysqli_num_rows($result_uuid) > 0) {
                  $note_uuid;
                  
                  // genarate a uuid to the note reply and make sure it is uniqer
                  $stmt_get = $mysqli->prepare("SELECT uuid FROM note_stack WHERE uuid=?");
                  do {
                        $note_uuid = bin2hex(random_bytes(8));
                
                        $stmt_get->bind_param("s", $note_uuid);
                        $stmt_get->execute();
                     
                        $result_uuid = $stmt_get->get_result();
                  } while (mysqli_num_rows($result_uuid) > 0);
                  
                  $stmt_reply = $mysqli->prepare("INSERT INTO note_stack (transmitter, text, uuid, reply) VALUES (?, ?, ?, ?)");
                  $stmt_reply->bind_param("ssss", $transmitter, $text, $note_uuid, $uuid);
                  $stmt_reply->execute();
                  
                  $stmt_reply->close();
              }
              $stmt_get->close();
         }
    }

    // change note read status
    function setNoteReadStatus($uuid, $recipient, $readed) {
        global $mysqli; 
        
        // check login ownership on $recipient before setting the note read status
        if (checkLoginOwnership($recipient, $_SESSION['password'])) {
            // set note read status at $uuid
                $stmt_send = $mysqli->prepare("UPDATE note_stack SET readed=? WHERE uuid=?");
                $stmt_send->bind_param("ss", $readed, $uuid);
                $stmt_send->execute();
                $stmt_send->close();
        }
    }
?>