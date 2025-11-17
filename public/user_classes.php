<?php
    class User {
        public function __construct($dataRow = null) {
            if(isset($dataRow)) {
                $this->uuid = $dataRow["uuid"];
                $this->username = $dataRow["username"];
                $this->tagline = $dataRow["tagline"];
                $this->biography = $dataRow["bio"];
                $this->dateOfBirth = $dataRow["date_of_birth"];
                $this->dateOfBirthVisible = $dataRow["date_of_birth_visible"];
                $this->gender = $dataRow["gender"];
                $this->genderVisible = $dataRow["gender_visible"];
                $this->registrationDate = $dataRow["registration_date"];
                $this->profileImage = $dataRow["profile_image"];
                $this->colorTheme = $dataRow["color_theme"];
                $this->land = $dataRow["land"];
                $this->landVisible = $dataRow["land_visible"];
            }
        }

        private $uuid = "";
        public function uuid() { 
            return $this->uuid;
        }

        private $username = "guest";
        public function username() {
            return $this->username;
        }

        private $tagline = "";
        public function tagline() {
            return $this->tagline;
        }

        private $biography = "";
        public function bography() {
            return $this->biography;
        }

        private $dateOfBirth = "";
        public function dateOfBirth() {
            if($this->dateOfBirthVisible)
                return $this->dateOfBirth;
        }

        private $dateOfBirthVisible = false;
        public function dateOfBirthVisible() {
            return $this->dateOfBirthVisible;
        }

        private $gender = "";
        public function gender() {
            if($this->genderVisible)
                return $this->gender;
        }

        private $genderVisible = false;
        public function genderVisible() {
            return $this->genderVisible;
        }
        
        private $registrationDate = "";
        public function registrationDate() {
            return $this->registrationDate;
        }

        private $profileImage = "./images/default_pp.webp";
        public function profileImage() { 
            return $this->profileImage;
        }

        private $lastVersionOfReadAndAccept = "";
        public function lastVersionOfReadAndAccept() {
            return $this->lastVersionOfReadAndAccept;
        }
        public function setLastVersionOfReadAndAccept($v) {
            $this->lastVersionOfReadAndAccept = $v;
        }

        private $colorTheme = "dark";
        public function colorTheme() { 
            return $this->colorTheme;
        }

        private $land = "";
        public function functionland() {
            if($this->landVisible)
                return $this->land;
        }

        private $landVisible = false;
        public function landVisible() {
            return $this->landVisible;
        }
    }

    class Login {
        public function __construct($email, $password) { 
            $this->email = $email;
            $this->password = $password;
        }

        private $email = "";
        public function email() {
            return $this->email;
        }

        private $password = "";
        public function password() {
            return $this->password;
        }
    }
?>