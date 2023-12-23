<?php   
    session_start();
    
    include_once '../mysql_functions/store_data.php';
    
    // get the json data of the post and send it to overwrite_post SESSION reqerst
    function editPost($key) {   
        // get object to edit
        $object = getS3Object($key, false);
        
        // verify that the user is the owner of the post
        if($object['Metadata']['owner'] != $_SESSION['uuid']) {
            if($_SESSION['overwrite_post'])
                unset($_SESSION['overwrite_post']);
            
            exit("could not verify that you are the owner of the file at key $key and therefore not able to continue the editing process...");
        }
        
        $_SESSION['overwrite_post'] = $key;
    }
?>