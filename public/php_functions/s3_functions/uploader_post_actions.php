<?php
    session_start();
    
    // Start output buffering
    ob_start();
    
    include_once '../mysql_functions/store_data.php';
    include_once 's3_client_handler.php';
    include_once '../data_filter.php';
    include_once "../s3_functions/object_loader.php";
    include_once '../s3_functions/delete_object.php';
    include_once 'file_uploader.php';
    
    // setup file data befor uploading the file to S3 bucket
    if($IAM_CLIENT != NULL && isset($_POST['data_type'])) {
        // timestamp
        $timestamp = date("o-n-d-H-i-s-") . microtime(true);
        
        // random file name
        $file_name = $timestamp . uniqid("", true);
        $file_name = removeAllNoneWordChars($file_name);
        
        try {
            $userFolder = $_SESSION['uuid'] . '/';
            $jsonDataKey;
            $data;
            $uploadOk = 1;
            
            // does user folder exist?
            if (!$s3->doesObjectExist($bucket, $userFolder)) {
                // folder does not exist, create it
                $s3->putObject([
                    'Bucket' => $bucket,
                    'Key' => $userFolder,
                    'Body' => ''
                ]);
           }
            
            // metadata
            // REMINDER the metadata index names ganna only be saved in small letters on AWS S3
            $metadata = array (
                'owner' => $_SESSION['uuid'],
                'data_type' => '',
                'in_two_versions' => 'false',
                'image_folder' => ''
            );
            
            // perform action based on the data type value of the post to upload
            // and check if it is a new post to upload or a old post to overwrite
            switch ($_POST['data_type']) {
                // if the post is a journal
                case 'journal':
                    // set data type in metadata
                    $metadata['data_type'] = 'json_text';
                    
                    // journal json file
                    $data = array (
                       'data_type' => $_POST['data_type'],
                       'title' => $_POST['title'],
                       'text' => $_POST['text']
                    );
                    
                    // check if it is a old journal that shold be overwrite or a new journal to upload
                    if (isset($_SESSION['overwrite_post'])) {
                        /* if it is a old journal that shold be overwrite */
                        
                        // get the old JSON file S3 data key
                        $jsonDataKey = $_SESSION['overwrite_post'];
                    }
                    else {
                        /* if it is a new journal that shold be stored */
                        
                        // make a new JSON file S3 data key
                        $jsonDataKey = $userFolder . 'json/' . $file_name . '.json';
                    }
                    
                    break;
                
                    // if the post is a image
                    case 'image':
                        // make a new image json file
                        $data = array (
                            'data_type' => $_POST['data_type'],
                            'image' => '',
                            'title' => $_POST['title'],
                            'text' => $_POST['text']
                        );

                        // check if it is a old image file that shold be overwrite or a new image to upload
                        if(isset($_SESSION['overwrite_post']) && isset($_FILES['image'])) {
                            /* if it is a old image file that shold be overwrite whith it's JSON data */

                            // delete the old image file but keep the JSON file
                            deletePost($_SESSION['overwrite_post'], true);

                            // get the old JSON file S3 data key
                            $jsonDataKey = $_SESSION['overwrite_post'];
                        }
                        else if (isset($_SESSION['overwrite_post'])) {
                            /* if it is only a old image json data that shold be overwrite (not the image file) */

                            // get the old object data from the old image file
                            $oldImageDataArray = null;
                            if(isset($_SESSION['overwrite_post']))
                                $oldImageDataArray = getS3Object($_SESSION['overwrite_post'], false);

                            $metadata = $oldImageDataArray['Metadata'];

                            // get the old JSON file S3 data key
                            $jsonDataKey = $_SESSION['overwrite_post'];

                            /* store the old image url in the new image json file */

                            $decoded_json = json_decode($oldImageDataArray['Body'], true);
                            $imageURL = urldecode($decoded_json['image']);

                            $data['image'] = $imageURL;
                        }
                        else {
                            /* if it is a new image file that shold be stored */

                            // make a new JSON file S3 data key
                            $jsonDataKey = $userFolder . 'json/' . $file_name . '.json';
                        }

                        // check if image file is a actual image or fake image
                        if(isset($_FILES['image'])) {
                            /* setup image data */

                            $temp_file_location = $_FILES['image']['tmp_name']; 
                            $imageFileType = strtolower(pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION));
                            $imageFolder = $file_name . '/';
                            $object_URL;

                            // set data type in metadata
                            $metadata['data_type'] = $imageFileType;

                            // check if file is a image and in certain file formats
                            if (isFileAImage($temp_file_location, $imageFileType, $acceptableFileTypes)) {
                                /* if everything is ok, try to handel the image file upload and upload the file */

                                $uploadFolder = $userFolder . $imageFolder;
                                // set image folder in metadata
                                $metadata['image_folder'] = $uploadFolder;

                                //upload the original file
                                $object_URL = uploadImageFile($temp_file_location, $uploadFolder, "high_res.$imageFileType", $metadata);

                                // resize the image (bool ref)
                                $IsImageResized = keepTheFileSizeBelowMax($temp_file_location);

                                if($IsImageResized == true) {
                                    $metadata['in_two_versions'] = 'true';

                                    //upload the resized file
                                    $object_URL = uploadImageFile($temp_file_location, $uploadFolder, "low_res.$imageFileType", $metadata);
                                }

                                // store image url in the image data
                                $data['image'] = $object_URL;
                            }
                            else
                                $uploadOk = 0;
                        }
                        else
                            echo "Sorry, your file was not uploaded.";
                        
                        break;
                
                    // if the post is a profile image
                    case 'profile_image':
                        // check if image file is a actual image or fake image
                        if(isset($_FILES['image'])) {
                            /* setup image data */

                            $temp_file_location = $_FILES['image']['tmp_name']; 
                            $imageFileType = strtolower(pathinfo(basename($_FILES["image"]["name"]), PATHINFO_EXTENSION));
                            $imageFolder = $file_name . '/';
                            $object_URL;

                            // set data type in metadata
                            $metadata['data_type'] = $imageFileType;

                            // check if file is a image and in certain file formats
                            if (isFileAImage($temp_file_location, $imageFileType, $acceptableFileTypes)) {
                                /* if everything is ok, try to handel the image file upload and upload the file */

                                $jsonDataKey = $userFolder . 'json/profile_image.json';

                                // if there is a profile image already, delete it and continue the upload process
                                if(getProfileImageJSON())
                                    deletePost($jsonDataKey);

                                $uploadFolder = $userFolder . "profile_image/";
                                // set image folder in metadata
                                $metadata['image_folder'] = $uploadFolder;

                                // resize the image (bool ref)
                                $IsResizedImage = keepTheFileSizeBelowMax($temp_file_location);

                                //upload the resized file
                                $object_URL = uploadImageFile($temp_file_location, $uploadFolder, "profile_image.$imageFileType", $metadata);

                                // profile image json file
                                $data = array (
                                    'data_type' => 'profile_image',
                                    'image' => $object_URL,
                                );
                            }
                            else
                                $uploadOk = 0;
                        }
                        
                        break;
                    
                     // if the post is a profile design
                    case 'profile_design':
                        $jsonDataKey = $userFolder . 'json/profile_design.json';
                        
                        // profile design json file
                        $data = array (
                            'data_type' => $_POST['data_type'],
                            'data' => $_POST['data']
                        );
                        
                        break;
                        
                    default :
                        exit('Error: no post type is set...');
                }
            
                // when the file is ready to be uploaded to S3 bucket
                // run this part of the script
                if(isset($data) && ($data != NULL) && ($uploadOk != 0)) {
                    // filter unwanted tags and attributes from text in post data and profile design
                    if($_POST['data_type'] != 'profile_design') {
                        $data['title'] = filterUnwantedCode($data['title']);
                        $data['text'] = filterUnwantedCode($data['text']);
                    }
                    else
                        $data['data'] = convertCustomProfileDesign(filterUnwantedCode($data['data']));
                    
                    // JSON encode post data
                    $json = json_encode($data);

                    // upload JSON post data
                    $result = $s3->putObject([
                        'Bucket' => $bucket,
                        'Key' => $jsonDataKey,
                        'Metadata' => $metadata,
                        'Body' => $json,
                        'ACL' => 'public-read'
                    ]);
                
                    // upload data to post_list if it is a new post to store
                    // and check the type of the post
                    if(!isset($_SESSION['overwrite_post']) && $_POST['data_type'] != "profile_image")
                        storePostData($result['ObjectURL'], $_POST['tags'], $_POST['data_type'], strip_tags($_POST['title']));
                    // overwrite a old post's data if it is not a new post to store (PS. only tags and title well be overwrited)
                    else if(isset($_SESSION['overwrite_post']))
                        overwritePostData($result['ObjectURL'], $_POST['tags'], strip_tags($_POST['title']));
                    // update main user profile image 
                    else if($_POST['data_type'] != "profile_design")
                        updateUserProfileInfo('', '', '', '', '', $data['image']);
                }
                else
                    exit('No post data was found...');

            // when done uploading, go to users profile
            header("Location: ../../profile.php?profile_id=" . $_SESSION['uuid']);
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
    else
        echo 'ERROR: Could not verify login ownership...';
    
    // Clear the output buffer and turn off output buffering
    ob_end_clean();

    // Send your headers here
    header('Content-Type: text/plain');
?>