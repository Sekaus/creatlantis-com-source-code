<?php
    include_once '../mysql_functions/store_data.php';
    include_once '../mysql_functions/load_content.php';
    
    /* perform action based on the comment command value */
    
    $command;
    $comment;
    $commentText;
    
    // TO-DO: make me more simples
    if (isset($_GET['comment_command']) && isset($_GET['stack_uuid'])) {
        $command = $_GET['comment_command'];
        $comment = $_GET['stack_uuid'];
        $commentText = $_GET['comment_text'];
        
        switch($command) {
           // perform edit action
           case 'edit_comment':
               editCommment(filterUnwantedCode($commentText), $comment);
               break;
           
           // perform delete action
           case 'delete_comment':
               removeComment($comment);
               break;
        }
        
        echo $command . " : " . $comment;
    }
    else
        echo "Error: comment command or comment not set.";
?>