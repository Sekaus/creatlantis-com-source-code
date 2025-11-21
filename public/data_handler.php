<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "./user_classes.php";
require_once "./third_party/aws/aws-autoloader.php";
require_once "./data_filter.php";

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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

    public function __destruct() {
        if (isset($this->mysqli)) $this->mysqli->close();
    }
}