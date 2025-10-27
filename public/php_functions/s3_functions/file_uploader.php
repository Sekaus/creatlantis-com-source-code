<?php
    /* Amason S3 SDK php 3 */
    
    require_once '../aws/aws-autoloader.php';

    use Aws\S3\S3Client;
    use Aws\S3\MultipartUploader;
    
    // AWS Info
    
    //login to S3 write
    $IAM_CLIENT = loginToS3();
    if($IAM_CLIENT != NULL) {
        // create an S3Client instance
        $s3 = new S3Client([
            'credentials' => [
                'key' => $IAM_CLIENT[0],
                 'secret' => $IAM_CLIENT[1]
                ],
                'version' => 'latest',
                'region' => 'eu-north-1',
                'debug' => true
            ]);
    }
    else
        echo 'ERROR: Could not verify login ownership...';
    
    // check if file is a image and in certain file formats
    function isFileAImage($temp_file_location, $imageFileType, $acceptableFileTypes) {
        $uploadOk = 1;
        
        // check if it is an image
        $check = getimagesize($temp_file_location);
        if($check == false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }
                        
        // only allow certain file formats
        if(!in_array($imageFileType, $acceptableFileTypes) ) {
            echo "Sorry, only " . implode(". ",$acceptableFileTypes) . " files are allowed.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            return false;
        }
        else
            return true;
    }

    // upload image file to AWS S3
    function uploadImageFile($filePath, $uploadFolder, $filename, $metadata) {
        global $IAM_CLIENT;

        // check if login ownership is verifed befor continue
        // else return NULL
        if($IAM_CLIENT == NULL)
            return NULL;
        
        global $s3;
        global $bucket;
        
        // does image file not already exist on the bucket
        $uploadKey = $uploadFolder . $filename;
        if (!$s3->doesObjectExist($bucket, $uploadKey)) {
            // create a multipart upload and make it
            $uploader = new MultipartUploader($s3, $filePath, [
                'Bucket' => $bucket,
                'Key' => $uploadKey,
                'Metadata' => $metadata,
                'ACL' => 'public-read',
            ]);

            try {
                $result = $uploader->upload();
            } catch (MultipartUploadException $e) {
                echo "Upload failed: " . $e->getMessage() . PHP_EOL;
            }
            return $result['ObjectURL'];
        }
        else
            return NULL;
    }
?>