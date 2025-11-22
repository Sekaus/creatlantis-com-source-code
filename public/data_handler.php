<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "./user_classes.php";
require_once "./third_party/aws/aws-autoloader.php";
require_once "./data_filter.php";

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;

enum FileType : string {
    case all = "all";
    case image = "image";
    case journal = "journal";
}

enum FileLoadOrder : string {
    case newest = "DESC";
    case oldest = "ASC";
}

enum S3BotType : string {
    case readOnly = "read";
    case writeOnly = "write";
}

class File {
    public function __construct(FileType $type, $metadata, $data) {
        $this->type = $type;
        $this->metadata = $metadata;
        $this->data = $data;
    }

    private FileType $type;
    public function type() : FileType { 
        return $this->type;
    }

    private $metadata;
    public function metadata() {
        return $this->metadata;
    }

    private $data;
    public function data() {
        return $this->data;
    }

    static $acceptableImage = array('gif', 'jpg', 'jpeg', 'pjpeg', 'png', 'svg', 'webp', 'pjpeg');
    static $maxFileSize = 1024 * 1024 * 5;

    // check if file is a image and in certain file formats
    public static function isFileAImage($tempFileLocation, $imageFileType) {
        $notAImageFile = false;

        // check if it is an image
        $check = getimagesize($tempFileLocation);
        if($check == false)  {
            error_log("File is not an image.");
            $notAImageFile = true;
        }
                        
        // only allow certain file formats
        if(!in_array($imageFileType, File::$acceptableImage) ) {
            error_log("Sorry, only " . implode(". ",File::$acceptableImage) . " files are allowed.");
            $notAImageFile = true;
        }

         return !$notAImageFile;
    }
}

/**
 * S3Wrapper - optimized S3 wrapper
 */
class S3Wrapper {
    private S3Client $s3;
    private string $bucket;

    public function __construct(array $credentials, string $bucket, string $region, bool $usePathStyle = false) {
        $this->bucket = $bucket;
        $this->s3 = new S3Client([
            'credentials' => $credentials,
            'region'      => $region,
            'version'     => 'latest',
            'use_path_style_endpoint' => $usePathStyle,
            'http' => [
                'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
                'connect_timeout' => 2,
                'timeout' => 5,
            ]
        ]);
    }

    public function listObjects(string $prefix): array {
        try {
            $prefix = rtrim($prefix, '/') . '/'; // Ensure trailing slash
            $result = $this->s3->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'MaxKeys' => 100 // limit to avoid massive listing
            ]);
            return $result['Contents'] ?? [];
        } catch (AwsException $e) {
            error_log("S3 listObjects failed for prefix '$prefix': " . $e->getMessage());
            return [];
        }
    }

    public function getPresignedUrl(string $key, string $expires = '+15 minutes'): ?string {
        try {
            $cmd = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ]);

            $request = $this->s3->createPresignedRequest($cmd, $expires);
            return (string)$request->getUri();

        } catch (AwsException $e) {
            error_log("S3 presigned URL failed for key '$key': " . $e->getMessage());
            return null;
        }
    }

    public function getObjectBodyAsString(string $key): ?string {
        try {
            $file = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key
            ]);
            return (string)$file['Body'];
        } catch (AwsException $e) {
            error_log("S3 getObjectBody failed for key '$key': " . $e->getMessage());
            return null;
        }
    }
    
    public function putObject(string $key, string $body, $metadata): bool {
        if(isset($this->s3)) {
            try {
                $this->s3->putObject([
                    "Bucket"  => $this->bucket,
                    "Key"     => $key,
                    'Metadata' => $metadata,
                    'Body' => $body,
                    'ACL' => 'public-read'
                ]);
            } catch (AwsException $e) {
                error_log("Fail to put object in the bucket: " . $e->getMessage());
                return false;
            }

            return true;
        }

        return false;
    }

    public function uploadFile(File $file, $uuid, $fileName = null) {
        try {
            // Metadata
            // REMINDER the metadata index names ganna only be saved in small letters on AWS S3
            $metadata = array (
                'owner'           => $_SESSION['uuid'],
                'data_type'       => '',
                'in_two_versions' => 'false',
            );

            // Random file name
            if(is_null($fileName)) {
                $timestamp = date("o-n-d-H-i-s-") . microtime(true);
                $fileName = $timestamp . uniqid("", true);
                $fileName = removeAllNoneWordChars($fileName);
            }

            if($file->data() != null) {
                switch ($file->type()) {
                    case FileType::image:
                        /* Setup image data */

                        $tempFileLocation = $file->data()['tmp_name'];
                        $imageFileType = strtolower(pathinfo(basename($file->data()["name"]), PATHINFO_EXTENSION));
                        $imageFolder = "$uuid/images/$fileName/";
                        $metadata['data_type'] = $imageFileType;

                        // Check if file is a image and in certain file formats
                        $resault = File::isFileAImage($tempFileLocation, $imageFileType);

                        if ($resault == true) {
                            /* if everything is ok, try to handel the image file upload and upload the file */

                            // Try upload the original file
                            if($this->uploadImageFileData($tempFileLocation, $imageFolder, "high_res.$imageFileType", $metadata)) {
                                // resize the image (bool ref)
                                $IsImageResized = keepTheFileSizeBelowMax($tempFileLocation);

                                if($IsImageResized == true) {
                                    $metadata['in_two_versions'] = 'true';

                                    //upload the resized file
                                    if(!$this->uploadImageFileData($tempFileLocation, $imageFolder, "low_res.$imageFileType", $metadata))
                                        error_log("Failed to upload the rescaled image file.");
                                }
                                
                                return $imageFolder;
                            }
                            else {
                                error_log("Failed to upload the image file.");
                                return null;
                            }
                        }

                        return null;

                    case FileType::journal:
                        /* Setup journal data */
                        
                        $title = filterUnwantedCode($file->data()["title"]);
                        $body = filterUnwantedCode($file->data()["body"]);
                        $journalKey = "$uuid/journals/$fileName.html";
                        $metadata['data_type'] = 'html';

                        // Journal body
                        $body = /*html*/`
                            <div>$title</div>
                            <div>$body</div>
                        `;

                        if($this->putObject($journalKey, $body, $metadata))
                            return $journalKey;

                        return null;
                }
            }
        } catch (AwsException $e) {
            error_log("Fail to upload file: ". $e->getMessage());

            return null;
        }
    }

    // Upload file data to AWS S3
    private function uploadImageFileData($filePath, $uploadFolder, $filename, $metadata) {
        if(isset($this->s3)) {
            $uploadKey = $uploadFolder . $filename;

            // Create a multipart upload and make it
            $uploader = new MultipartUploader($this->s3, $filePath, [
                'Bucket' => $uploadKey,
                'Key' => $uploadFolder,
                'Metadata' => $metadata,
                'ACL' => 'public-read',
            ]);

            try {
                $result = $uploader->upload();
            } catch (MultipartUploadException $e) {
                error_log("Upload failed: " . $e->getMessage() . PHP_EOL);
                return false;
            }

            return true;
        }

        return false;
    }
}

/**
 * DataHandle - handles DB + S3 interactions
 */
class DataHandle {
    private \mysqli $mysqli;
    private S3Wrapper $s3;
    private S3BotType $s3BotType;

    public function __construct(array $dbConfig, array $s3Config, S3BotType $botType) {
        $this->mysqli = new \mysqli(
            $dbConfig['host'] ?? 'localhost',
            $dbConfig['username'] ?? 'root',
            $dbConfig['password'] ?? '',
            $dbConfig['database'] ?? '',
            $dbConfig['port'] ?? 3306
        );

        if ($this->mysqli->connect_error) {
            throw new \RuntimeException("DB connection failed: " . $this->mysqli->connect_error);
        }

        $stmt = $this->mysqli->prepare("SELECT `key`, `secret_key` FROM s3_bot_keys WHERE type = ? LIMIT 1");
        $typeValue = $botType->value;
        $stmt->bind_param("s", $typeValue);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || empty($row['key']) || empty($row['secret_key'])) {
            throw new \RuntimeException("S3 keys not found for bot type: {$typeValue}");
        }

        $this->s3 = new S3Wrapper(
            ['key' => $row['key'], 'secret' => $row['secret_key']],
            $s3Config['bucket_or_arn'] ?? throw new \InvalidArgumentException("S3 bucket required"),
            $s3Config['region'] ?? throw new \InvalidArgumentException("S3 region required"),
            $s3Config['use_path_style'] ?? false
        );

        $this->s3BotType = $botType;
    }

    public function loginAsUser(string $email, string $password): bool {
        $stmt = $this->mysqli->prepare("SELECT * FROM user_info WHERE email=? AND password=PASSWORD(?) LIMIT 1");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $_SESSION["login"] = serialize(new Login($email, $password));
            $_SESSION["user_data"] = serialize(new User($row));
            return true;
        }
        return false;
    }

    public function verifyOwnership(string $email, string $password, string $username): bool {
        $stmt = $this->mysqli->prepare("SELECT uuid FROM user_info WHERE username = ? AND email = ? AND password = PASSWORD(?) LIMIT 1");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $owned = ($stmt->get_result()->num_rows > 0);
        $stmt->close();
        return $owned;
    }

    public static function logout(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    private function resolveImageRes(array $objects, bool $wantsHigh): ?string {
        $low = $high = null;

        foreach ($objects as $key) {
            if (preg_match('#/low_res\.[a-zA-Z0-9]+$#', $key)) $low = $key;
            if (preg_match('#/high_res\.[a-zA-Z0-9]+$#', $key)) $high = $key;
        }

        return $wantsHigh ? ($high ?? $low) : ($low ?? $high);
    }

    public function loadAllFiles(FileType $filter, string $search, FileLoadOrder $order, int $maxKeys = 50, int $offset = 0): string {
        $searchLike = '%' . $this->mysqli->real_escape_string($search) . '%';
        $orderString = $order->value;
        $limit = max(1, $maxKeys);
        $offset = max(0, $offset);

        if ($filter === FileType::all) {
            $stmt = $this->mysqli->prepare("SELECT * FROM post_list WHERE (tags LIKE ? OR title LIKE ?) ORDER BY date {$orderString} LIMIT ? OFFSET ?");
            $stmt->bind_param("ssii", $searchLike, $searchLike, $limit, $offset);
        } else {
            $typeVal = $filter->value;
            $stmt = $this->mysqli->prepare("SELECT * FROM post_list WHERE type = ? AND (tags LIKE ? OR title LIKE ?) ORDER BY date {$orderString} LIMIT ? OFFSET ?");
            $stmt->bind_param("sssii", $typeVal, $searchLike, $searchLike, $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $items = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row['key'] ?? '';
            $type = $row['type'] ?? '';

            if (!$key) continue;

            if ($type === FileType::image->value) {
                $objects = $this->s3->listObjects($key);
                $keys = array_column($objects, 'Key');

                $finalKey = $this->resolveImageRes($keys, false);

                if ($finalKey) {
                    $src = $this->s3->getPresignedUrl($finalKey);
                    $items[] = ['type' => 'image', 'src' => $src, 'title' => $row['title']];
                }
            } elseif ($type === FileType::journal->value) {
                $body = $this->s3->getObjectBodyAsString($key);
                if ($body !== null) {
                    $items[] = ['type' => 'journal', 'body' => $body, 'title' => $row['title']];
                }
            }
        }

        $stmt->close();
        return json_encode($items);
    }

    public function uploadFile(File $file, Login $login, User $user) {
        if($this->verifyOwnership($login->email(), $login->password(), $user->username())) {
            // Store the file on the file server and return it's key
            $key = $this->s3->uploadFile($file, $user->username());
            
            // Store the key on the post list of userdb
            if ($key == null) {
                $sql = 'INSERT INTO post_listb ("key", "tags", "type", "owner", title) VALUES (?, ?, ?, ?, ?)';
                $stmt = $this->mysqli->prepare( $sql );

                $tags = filterUnwantedCode($file->metadata()['tags']);
                $type = filterUnwantedCode($file->metadata()['type']);
                $owner = filterUnwantedCode($user->uuid());
                $title = filterUnwantedCode($file->metadata()['title']);

                $stmt->bind_param('sssss', $key, $tags, $type, $owner, $title);
                $stmt->execute();
            }
            else
                error_log("The user has the proper permissions to upload files, but thay somehow can't.");
        }
        else
            error_log('It appears that this user ' . $user->username() . '/' . $user->uuid() . ' does not have the proper permissions to upload files.');
    }

    public function __destruct() {
        if (isset($this->mysqli)) $this->mysqli->close();
    }
}