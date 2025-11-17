<?php
    session_start();

    include_once("./user_classes.php");

    class DataHandle {
        private $mysqli;

        public function __construct() {
            $host = "localhost";
            $username = "root";
            $password = "Test-13579";
            $database = "userdb";
            $port = 3306;

            // Create connection
            $mysqli = new mysqli($host, $username, $password, $database, $port);

            // Check connection
            if ($mysqli->connect_error) {
                die("Connection failed: " . $mysqli->connect_error);
            }
        }

        public function loginAsUser($email, $password) {
            if (isset($mysqli)) {
                $sql = "SELECT * FROM user_info WHERE email=? AND password=PASSWORD(?)";
                $stmt = $this->$mysqli->prepare($sql);
                $stmt->bind_param("ss", $email, $password);
                $stmt->execute();
                
                $result = $stmt->get_result();

                if(mysqli_num_rows($result) > 0)  {
                    $login = new Login($email, $password);
                    $_SESSION["login"] = serialize(login);

                    $user = new User($result->fetch_assoc());
                    $_SESSION["user_data"] = serialize($user);
                }
            }
        }

        // Check ownership of the viewed profile
        function verifyOwnership(Login $login, $username ) { 
            if (isset($mysqli)) {
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
            if(isset($mysqli))
                $mysqli->close();
        }
    }
?>