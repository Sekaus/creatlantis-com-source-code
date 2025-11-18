<?php
    session_start();

    include_once("./user_classes.php");

    enum FileType : string {
        case all = "all";
        case image = "image";
        case journal = "journal";
    }

    enum FileLoadOrder: string {
        case newest = "DESC";   // Newest first
        case oldest = "ASC";    // Oldest first
    }

    class DataHandle {
        private $mysqli;

        public function __construct() {
            $host = "localhost";
            $username = "root";
            $password = "Test-13579";
            $database = "userdb";
            $port = 3306;

            // Create connection
            $this->mysqli = new mysqli($host, $username, $password, $database, $port);

            // Check connection
            if ($this->mysqli->connect_error) {
                die("Connection failed: " . $this->mysqli->connect_error);
            }
        }

        public function loginAsUser($email, $password) {
            if (isset($this->mysqli)) {
                $sql = "SELECT * FROM user_info WHERE email=? AND password=PASSWORD(?)";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param("ss", $email, $password);
                $stmt->execute();
                
                $result = $stmt->get_result();

                if(mysqli_num_rows($result) > 0)  {
                    $login = new Login($email, $password);
                    $_SESSION["login"] = serialize($login);

                    $user = new User($result->fetch_assoc());
                    $_SESSION["user_data"] = serialize($user);
                }
            }
        }

        // Check ownership of the viewed profile
        function verifyOwnership(Login $login, $username ) { 
            if (isset($$this->mysqli)) {
                $sql = "SELECT uuid FROM user_info WHERE username=? AND email=? AND password=PASSWORD(?)";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param("sss", $login);
                $stmt->execute();
            
                $result = $stmt->get_result();

                $isTheOwner = false;

                // test if user by email has ownership of the login
                if(mysqli_num_rows($result) > 0)
                    $isTheOwner = true;
                
                $stmt->close();

                return $isTheOwner;
            }

            return false;
        }

        public static function logout() { 
            if(isset($_SESSION["login"]))
                session_destroy();
        }

        public function __destruct() {
            if(isset($this->mysqli))
                $this->mysqli->close();
        }

        function loadAllFiles(FileType $filter, string $search, FileLoadOrder $order, int $maxKeys, int $offset) {
            if (isset($this->mysqli)) {
                $orderString = $order->value;
                
                $filterString = "type LIKE ? AND (tags LIKE ? OR title LIKE ?)";
                if ($filter == FileType::all)
                    $filterString = "tags LIKE ? OR title LIKE ?";

                $sql = "SELECT * FROM post_list WHERE $filterString ORDER BY date $orderString LIMIT ? OFFSET ?";
                $stmt = $this->mysqli->prepare($sql);

                if ($filter != FileType::all)
                    $stmt->bind_param("sssii", $filter->value, $search, $search, $maxKeys, $offset);
                else
                    $stmt->bind_param("ssii",  $search, $search, $maxKeys, $offset);

                $stmt->execute();

                $result = $stmt->get_result();

                // output data of each row

                $json = "[ ";

                while ($row = $result->fetch_assoc()) {
                    if($row['id'] >= 0) {
                        $json .= json_encode($row) . ", ";
                        
                        //$key = strchr($row['link'] , $row['owner']);
                        //echo loadS3Object($key);
                    }
                }

                $json .= " ]";

                $result->close();

                return $json;
            }
        }
    }
?>