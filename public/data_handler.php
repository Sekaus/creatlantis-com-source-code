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
 * S3Wrapper - safe wrapper around Aws\S3\S3Client
 */
class S3Wrapper {
    private S3Client $s3;
    private string $bucket;

    public function __construct(
        array $credentials,
        string $bucket,
        string $region,
        bool $usePathStyle = false
    ) {
        $this->bucket = $bucket;

        $config = [
            'credentials' => [
                'key' => $credentials['key'],
                'secret' => $credentials['secret'],
            ],
            'region' => $region,
            'version' => 'latest',
            'use_path_style_endpoint' => $usePathStyle,
            'http' => [
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                ],
                'connect_timeout' => 2,
                'timeout' => 5,
            ]
        ];

        $this->s3 = new S3Client($config);
    }

    public function getObjectBodyAsString(string $key): string {
        // try get the object if it exist
        if($this->objectExists($key)) {
            $file = $this->s3->getObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        
            // return the s3 object JSON data
            return $file['Body'];
        }
        else
            return "file at key $key dose not exist or cloude be found...";
    }

    public function getObjectUrl(string $key): ?string {
        if($this->objectExists($key)) {
            try {
                // Get the plain URL for the object
                return $this->s3->getObjectUrl($this->bucket, $key);
            } catch (AwsException $e) {
                error_log($e->getMessage());
                return null;
            }
        }
        else {
            error_log("Object at key: $key, does not exist...");
            return null;
        }
    }

    public function objectExists(string $key): bool {
        try {
            return $this->s3->doesObjectExistV2($this->bucket, $key);
        } catch (\Throwable $e) {
            error_log("S3 objectExists failed for key '$key': " . $e->getMessage());
            return false;
        }
    }
}

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

        // Load S3 credentials from DB table `s3_bot_keys`
        $stmt = $this->mysqli->prepare("SELECT `key`, `secret_key` FROM s3_bot_keys WHERE type = ? LIMIT 1");
        $typeValue = $botType->value;
        $stmt->bind_param("s", $typeValue);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row || empty($row['key']) || empty($row['secret_key'])) {
            throw new \RuntimeException("S3 keys not found for bot type: {$typeValue}");
        }

        if (empty($s3Config['bucket_or_arn'])) {
            throw new \InvalidArgumentException("S3 bucket required in s3Config");
        }

        $this->s3 = new S3Wrapper(
            ['key' => $row['key'], 'secret' => $row['secret_key']],
            $s3Config['bucket_or_arn'],
            $s3Config['region'] ?? throw new \InvalidArgumentException("S3 region required"),
            $s3Config['use_path_style'] ?? false
        );

        $this->s3BotType = $botType;
    }

    public function loginAsUser(string $email, string $password): bool {
        $sql = "SELECT * FROM user_info WHERE email=? AND password=PASSWORD(?) LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $_SESSION["login"] = serialize(new Login($email, $password));
            $_SESSION["user_data"] = serialize(new User($row));
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function verifyOwnership(string $email, string $password, string $username): bool {
        $sql = "SELECT uuid FROM user_info WHERE username = ? AND email = ? AND password = PASSWORD(?) LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $res = $stmt->get_result();
        $owned = ($res && $res->num_rows > 0);
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

    public function loadAllFiles(FileType $filter, string $search, FileLoadOrder $order, int $maxKeys = 50, int $offset = 0): string {
        $searchLike = '%' . $this->mysqli->real_escape_string($search) . '%';
        $orderString = $order->value;
        $limit = max(1, $maxKeys);
        $offset = max(0, $offset);

        if ($filter === FileType::all) {
            $sql = "SELECT * FROM post_list WHERE (tags LIKE ? OR title LIKE ?) ORDER BY date {$orderString} LIMIT ? OFFSET ?";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("ssii", $searchLike, $searchLike, $limit, $offset);
        } else {
            $typeVal = $filter->value;
            $sql = "SELECT * FROM post_list WHERE type = ? AND (tags LIKE ? OR title LIKE ?) ORDER BY date {$orderString} LIMIT ? OFFSET ?";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("sssii", $typeVal, $searchLike, $searchLike, $limit, $offset);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $items = [];

        while ($row = $result->fetch_assoc()) {
            $key = $row['key'] ?? '';
            $owner = $row['owner'] ?? '';

            if ($key === null) {
                error_log("Failed to extract get the key from post: {$key}");
                continue;
            }

            $body = $this->s3->getObjectBodyAsString($key);
            if ($body === null) {
                error_log("Skipping object due to missing body: {$key}");
                continue;
            }

            $decoded = json_decode($body, true);
            $items[] = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) 
                ? $decoded 
                : [
                    'raw'  => $body, 
                    'meta' => [
                        'id'    => $row['id'] ?? null,
                        'title' => $row['title'] ?? null,
                        'owner' => $owner,
                        'key'   => $key,
                        'type'  => $row['type'] ?? null,
                        'date'  => $row['date'] ?? null,
                    ]
                ];
        }

        $stmt->close();
        return json_encode($items, JSON_UNESCAPED_UNICODE);
    }

    public function __destruct() {
        if (isset($this->mysqli)) $this->mysqli->close();
    }
}