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
            
        }

        // Check ownership of the viewed profile
        function verifyOwnership(Login $login, $username ) { 
            $sql = "SELECT uuid FROM user_info WHERE username=? AND email=? AND password=PASSWORD(?)";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("sss", $login);
        }

        public function __destruct() {
            if(isset($mysqli))
                $mysqli->close();
        }
    }
?>