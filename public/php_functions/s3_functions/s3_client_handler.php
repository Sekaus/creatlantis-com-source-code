<?php
    $absolute_path = dirname(__FILE__);
    include_once "$absolute_path/../mysql_functions/login_handler.php";
    
    /* Amason S3 SDK php login to s3 client 3 */
    $bucket = 'creatlantis.com.s3.local';
    
    // get S3 keys
    $s3keys = $mysqli->query("SELECT * FROM s3_bot_keys");
    $readKeys = $s3keys->fetch_assoc();
    $writeKeys = $s3keys->fetch_assoc();
    
    // S3 read keys
    function loginToS3Read() {
        global $readKeys;
        
        $IAM_KEY = $readKeys['key'];
        $IAM_SECRET = $readKeys['secret_key'];

        return [$IAM_KEY, $IAM_SECRET]; 
    }

    // S3 write keys
    // TO-DO: rename me to loginToS3Write
    function loginToS3() {
        if(verifyLogin($_SESSION['uuid'], $_SESSION['password'])) {
            global $writeKeys;
        
            $IAM_KEY = $writeKeys['key'];
            $IAM_SECRET = $writeKeys['secret_key'];

            return [$IAM_KEY, $IAM_SECRET]; 
        }
        else
            return NULL;
    }
?>