<?php
    include_once 'edit_object.php';
    include_once 'delete_object.php';
    include_once 'object_loader.php';
    
    /* perform action based on the post command value */
    
    $command;
    $post;
    
    if (isset($_GET['post_command']) && isset($_GET['post'])) {
        $command = $_GET['post_command'];
        $post = $_GET['post'];
        
        switch($command) {
           //perform edit action
           case 'edit_post':
               editPost($post);
               break;
           
           //perform delete action
           case 'delete_post':
               deletePost($post);
               break;
        }
        
        echo $command . " : " . $post;
    } 
    else
        echo "Error: post command or post not set.";
?>