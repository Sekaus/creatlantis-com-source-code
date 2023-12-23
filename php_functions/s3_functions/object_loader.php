<?php
    session_start();

    $absolute_path = dirname(__FILE__);
    include_once "$absolute_path/../../php_functions/s3_functions/s3_client_handler.php";
    
    require_once "$absolute_path/../../php_functions/aws/aws-autoloader.php";
    
    use Aws\S3\S3Client;
    use Aws\Exception\AwsException;
    use GuzzleHttp\Psr7\Stream;
    
    // create an S3Client (read) instance
    $IAM_CLIENT = loginToS3Read();
    $s3 = new S3Client([
        'credentials' => [
            'key' => $IAM_CLIENT[0],
            'secret' => $IAM_CLIENT[1]
        ],
        'version' => 'latest',
        'region' => 'eu-north-1'
    ]);
    
    //return a file from AWS S3
    function getS3Object($key, $returnAsJSON = true) {
        global $bucket;
        global $s3;

        // try get the object if it exist
        if($s3->doesObjectExist($bucket, $key)) {
            $file = $s3->getObject([
                'Bucket' => $bucket,
                'Key' => $key,
            ]);
        
            // return the s3 object ref or JSON data
            if($returnAsJSON)
                return $file['Body'];
            else
                return $file;
        }
        else
            return "file at key $key dose not exist or cloude be found...";
    }
    
    // try get the profile image JSON if it exist
    function getProfileImageJSON() {
        global $bucket;
        global $s3;
        
        // does profile image json file exist?
        // if the JSON file exist, return true
        $jsonFileKey = $_SESSION['uuid'] . "/json/profile_image.json";
        if ($s3->doesObjectExist($bucket, $jsonFileKey))
            return true;
        // if not, return false
        else
            return false;
    }
    
    // load a users content
    function loadS3Object($key, $fullSize = false) {
        // if found then return the json in a JavaScript call
        $file = getS3Object($key, false);
        
        if(isset($file['Body']))
            return 'loadPost(' . $file['Body'] .', "' . $key . '",' . $fullSize . ');';
        else
            echo '<div class="post-block">ERROR 404</div>';
    }
    
    // load a date in as text
    function loadDate($date) {
        return "loadDate('$date');";
    }
    
    // load a tag in as a link
    function loadTag($tag) {
        // check if tag is empty
        // if not
        if($tag != "")
            return "loadTag('#$tag');";
        // if it is
        else
            return "";
    }
?>