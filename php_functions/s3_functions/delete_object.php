<?php
    session_start();
    
    include_once 's3_client_handler.php';
    include_once '../mysql_functions/store_data.php';
    
    /* Amason S3 SDK php 3 */
    
    require_once '../aws/aws-autoloader.php';

    use Aws\S3\S3Client;
    
    // delete post at key
    // you can ask for keeping the JSON data of it if you like (and it's data store)
    function deletePost($key, $keepJSON = false) {
        global $bucket;

        // starting by verify login
        $AMI = loginToS3();
        if ($AMI != NULL) {
            $s3 = new S3Client([
                'credentials' => [
                    'key' => $AMI[0],
                    'secret' => $AMI[1]
                ],
                'version' => 'latest',
                'region' => 'eu-north-1'
            ]);

            // get the object to delete
            $object = getS3Object($key, false);
            
            // verify that the main user is the owner of the post
            if($object['Metadata']['owner'] != $_SESSION['uuid'])
                exit("could not verify that you are the owner of the file at key $key and therefore not able to continue the deleting process...");

            // get post type and full link
            $postType;
            $fullLink;
            try {
                $jsonArray = json_decode($object['Body']);
                $postType = $jsonArray->data_type;
                $fullLink = $s3->getObjectUrl($bucket, $key);
            } catch (ErrorException $e) {
                exit("Error: invalid object");
            }

            try {
                // is post type a journal
                if ($postType == 'journal') {
                    /* delete a journal post or a profile image from the bucket. */

                    startDeleteing($key, $s3);
                }
                
                // is post type a image or profile image
                else if ($postType == 'image' || $postType == 'profile_image') {
                    /* delete a image post from the bucket (both low_res and high_res) */
                    
                    $uploadFolder = $object['Metadata']['image_folder'];
                    $imageDataType = $object['Metadata']['data_type'];
                    
                    // before start deleteing the image (high_res and low_res) make sure that all the necessary metadata is set
                    if($uploadFolder == "" || $imageDataType == "") {
                        /* if some of them are missing */
                        echo "Error: unable to delete the post...\n";
                        echo "Post at key: '$key' is missing some matadata";
                        exit;
                    }
                    
                    /* if all the necessary metadata is set */
                    
                    // check if it is a image or profile image
                    if($postType == 'image') {
                        /* if it is a image (not a profile image) */
                        
                        startDeleteing($uploadFolder . "high_res.$imageDataType", $s3);

                        if($object['Metadata']['in_two_versions'] == 'true')
                            startDeleteing($uploadFolder . "low_res.$imageDataType", $s3);
                    }
                    else {
                        /* if it is a profile image */
                        
                        startDeleteing($uploadFolder . "profile_image.$imageDataType", $s3);
                    }

                    // only remove the JSON data if it is not needed anymore
                    if(!$keepJSON)
                        //remove the image json file
                        startDeleteing($key, $s3);
                }
            } catch (S3Exception $e) {
                echo 'Error: ' . $e->getAwsErrorMessage() . PHP_EOL;
            }

            // check to see if the post was deleted.
            if (!$s3->doesObjectExist($bucket, $key)) {
                // delete object from post list
                removePostData($fullLink);
            }
        } 
        else
            echo 'fail to verify login...';
    }
    
    //delete a single object from AWS S3 bucket
    function startDeleteing($key, $s3) {
        global $bucket;
        
        try {
            //try to delete object at key
            $result = $s3->deleteObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);
            
            //check if the object at key are deletet
            if ($result['DeleteMarker']) {
                echo $key . ' was deleted or does not exist.' . PHP_EOL;
            } else {
                echo 'Error: ' . $key . ' was not deleted.' . PHP_EOL;
            }
        } catch (S3Exception $e) {
            echo 'Error: ' . $e->getAwsErrorMessage() . PHP_EOL;
        }
    }
?>