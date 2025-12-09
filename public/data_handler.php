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
        $check = false;

        if(isset($tempFileLocation))
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
    
    public function putObject(string $key, string $body, $metadata = null): bool {
        if(isset($this->s3)) {
            try {
                if($metadata !== null) {
                    $this->s3->putObject([
                        "Bucket"  => $this->bucket,
                        "Key"     => $key,
                        'Metadata' => $metadata,
                        'Body' => filterUnwantedCode($body)
                    ]);
                }
                else {
                    $this->s3->putObject([
                        "Bucket"  => $this->bucket,
                        "Key"     => $key,
                        'Body' => filterUnwantedCode($body)
                    ]);
                }
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
                'owner'           => $uuid,
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
                        
                        $title = convertQuotesToUnicode(filterUnwantedCode($file->data()["title"]));
                        $body = convertQuotesToUnicode(filterUnwantedCode($file->data()["body"]));
                        $journalKey = "$uuid/journals/$fileName.html";
                        $metadata['data_type'] = 'html';

                        // Journal body
                        $body = <<<HTML
                            <div class='big-text'>$title</div>
                            <div>$body</div>
                        HTML;

                        $cleanBody = trim($body);
                        $cleanBody = mb_convert_encoding($cleanBody, 'UTF-8', 'UTF-8');

                        if ($this->putObject($journalKey, $cleanBody, $metadata))
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
            $uploadKey = rtrim($uploadFolder, '/') . '/' . $filename;

            // Create a multipart upload and make it
            $uploader = new MultipartUploader($this->s3, $filePath, [
                'Bucket' => $this->bucket,
                'Key' => $uploadKey,
                'Metadata' => $metadata
            ]);

            try {
                $result = $uploader->upload();
                return true;
            } catch (MultipartUploadException $e) {
                error_log("Upload failed: " . $e->getMessage() . PHP_EOL);
                return false;
            }
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

    public function __construct(array $dbConfig, array $s3Config, ?S3BotType $botType = null) {
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

        if($botType !== null) {
            // Load S3 credentials only when needed
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
    }

    public function loginAsUser(string $email, string $password): bool {
        $stmt = $this->mysqli->prepare("SELECT * FROM user_info WHERE email=? AND password=PASSWORD(?) LIMIT 1");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $_SESSION["user_login"] = serialize(new Login($email, $password));
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

    public function updateProfileDesign(User $user, Login $login, string $json) {
        if ($this->verifyOwnership($login->email(), $login->password(), $user->username())) {
            $key = $user->uuid() ."/profile_design.json";
            
            if ($this->s3->putObject($key, $json))
                return ['success' => true];    
            else
                return ['success' => false, 'error' => 'Failed update profile.'];
        }
        else
            return ['success' => false, 'error' => 'Not authorized.'];
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

    public function getUserInfo($username) : User {
        $stmt = $this->mysqli->prepare("SELECT * FROM user_info WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return new User($result->fetch_assoc());
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
                    $items[] = ['type' => 'journal', 'body' => preg_replace('/[\\x00-\\x1F\\x7F]/', '', $body), 'title' => $row['title']];
                }
            }
        }

        $stmt->close();
        return json_encode($items, );
    }

    public function GetURLOnSingleFile($key): string {
        if (!$key) return "";
        
        return $this->s3->getPresignedUrl($key);;
    }

    public function GetBodyAsStringOnSingleFile($key): ?string {
        if (!$key) return "";

        return $this->s3->getObjectBodyAsString($key);
    }

    public function uploadFile(File $file, Login $login, User $user) {
        // Validate types (login/user may be serialized objects from session)
        if (!($login instanceof Login) || !($user instanceof User))
            return ['success' => false, 'error' => 'Invalid login/user.'];

        // Verify ownership
        if (!$this->verifyOwnership($login->email(), $login->password(), $user->username()))
            return ['success' => false, 'error' => 'Not authorized.'];

        // Upload to S3 (returns key or null)
        $key = $this->s3->uploadFile($file, $user->uuid());
        if ($key === null) {
            error_log("S3 upload failed for user {$user->uuid()}");
            return ['success' => false, 'error' => 'S3 upload failed.'];
        }

        // Save DB record (use the same table you read from - I changed post_listb -> post_list)
        $sql = "INSERT INTO post_list (`key`, `tags`, `type`, `owner`, `title`) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            error_log("MySQL prepare failed: " . $this->mysqli->error);
            return ['success' => false, 'error' => 'DB prepare failed: ' . $this->mysqli->error];
        }

        $tags  = filterUnwantedCode($file->metadata()['tags'] ?? '');
        $type  = filterUnwantedCode($file->metadata()['type'] ?? '');
        $owner = filterUnwantedCode($user->uuid() ?? '');
        $title = filterUnwantedCode($file->metadata()['title'] ?? '');

        $stmt->bind_param('sssss', $key, $tags, $type, $owner, $title);

        if (!$stmt->execute()) {
            error_log("DB execute failed: " . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'DB execute failed: ' . $stmt->error];
        }

        $stmt->close();

        // Return success + key (caller will JSON-encode)
        return ['success' => true, 'key' => $key];
    }

    public function updateUserInfo(User $user, Login $login) {
        // Validate types (login/user may be serialized objects from session)
        if (!($login instanceof Login) || !($user instanceof User)) {
            return ['success' => false, 'error' => 'Invalid login/user.'];
        }

        // Verify ownership (this will prevent unauthorized updates)
        if (!$this->verifyOwnership($login->email(), $login->password(), $user->username())) {
            return ['success' => false, 'error' => 'Not authorized.'];
        }

        // Collect sanitized values
        $username = filterUnwantedCode($user->username());
        $tagline = filterUnwantedCode($user->tagline());
        $biography = filterUnwantedCode($user->biography());
        
        $unfilteredData = $user->unfilteredData($login);
        if ($unfilteredData === null) {
            return ['success' => false, 'error' => 'Failed to get unfiltered data and had to exit.'];
        }

        $dateOfBirth = filterUnwantedCode($unfilteredData['dateOfBirth'] ?? '');
        // Convert visibility flags to integer 0/1
        $dateOfBirthVisible = $user->dateOfBirthVisible() ? 1 : 0;
        $gender = filterUnwantedCode($unfilteredData['gender'] ?? '');
        $genderVisible = $user->genderVisible() ? 1 : 0;
        $profileImage = filterUnwantedCode($user->profileImage(false) ?? '');
        $lastVersionOfReadAndAccept = filterUnwantedCode($user->lastVersionOfReadAndAccept() ?? '');
        $colorTheme = filterUnwantedCode($user->colorTheme() ?? '');
        $land = filterUnwantedCode($unfilteredData['land'] ?? '');
        $landVisible = $user->landVisible() ? 1 : 0;
        $hobbies = filterUnwantedCode($user->hobbies() ?? '');

        // Use uuid in WHERE (safer and simpler than matching password in WHERE)
        $whereUuid = filterUnwantedCode($user->uuid());

        $sql = "
            UPDATE user_info SET 
                username = ?, 
                tagline = ?, 
                bio = ?, 
                date_of_birth = ?, 
                date_of_birth_visible = ?, 
                gender = ?, 
                gender_visible = ?, 
                profile_image = ?, 
                last_version_of_read_and_accept = ?, 
                color_theme = ?,
                land = ?,
                land_visible = ?,
                hobbies = ?
            WHERE uuid = ?
            LIMIT 1
        ";

        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            error_log("MySQL prepare failed in updateUserInfo: " . $this->mysqli->error);
            return ['success' => false, 'error' => 'DB prepare failed.'];
        }

        // Bind everything as strings for simplicity (booleans were converted to 0/1 already)
        $types = str_repeat('s', 14); // 13 SET fields + 1 WHERE uuid
        $bindResult = $stmt->bind_param(
            $types,
            $username,
            $tagline,
            $biography,
            $dateOfBirth,
            $dateOfBirthVisible,
            $gender,
            $genderVisible,
            $profileImage,
            $lastVersionOfReadAndAccept,
            $colorTheme,
            $land,
            $landVisible,
            $hobbies,
            $whereUuid
        );

        if (!$bindResult) {
            error_log("MySQL bind_param failed in updateUserInfo: " . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'DB bind failed.'];
        }

        if (!$stmt->execute()) {
            error_log("MySQL execute failed in updateUserInfo: " . $stmt->error);
            $stmt->close();
            return ['success' => false, 'error' => 'DB execute failed.'];
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            // Not necessarily an error (maybe nothing changed), but report it
            return ['success' => true, 'updated' => false, 'message' => 'No rows changed.'];
        }

        return ['success' => true, 'updated' => true, 'rows' => $affected];
    }

    public function __destruct() {
        if (isset($this->mysqli)) $this->mysqli->close();
    }
}