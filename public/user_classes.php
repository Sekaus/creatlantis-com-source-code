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

        private string $uuid = "";
        public function uuid() { 
            return $this->uuid;
        }

        private string $username = "guest";
        public function username() {
            return $this->username;
        }

        private string $tagline = "";
        public function tagline() {
            return $this->tagline;
        }

        private string $biography = "";
        public function bography() {
            return $this->biography;
        }

        private string $dateOfBirth = "";
        public function dateOfBirth() {
            if($this->dateOfBirthVisible)
                return $this->dateOfBirth;
        }

        private bool $dateOfBirthVisible = false;
        public function dateOfBirthVisible() {
            return $this->dateOfBirthVisible;
        }

        private string $gender = "";
        public function gender() {
            if($this->genderVisible)
                return $this->gender;
        }

        private bool $genderVisible = false;
        public function genderVisible() {
            return $this->genderVisible;
        }
        
        private string $registrationDate = "";
        public function registrationDate() {
            return $this->registrationDate;
        }

        private string $profileImage = "./images/default_pp.webp";
        public function profileImage() { 
            return $this->profileImage;
        }

        private string $lastVersionOfReadAndAccept = "";
        public function lastVersionOfReadAndAccept() {
            return $this->lastVersionOfReadAndAccept;
        }
        public function setLastVersionOfReadAndAccept($v) {
            $this->lastVersionOfReadAndAccept = $v;
        }

        private string $colorTheme = "dark";
        public function colorTheme() { 
            return $this->colorTheme;
        }

        private string $land = "";
        public function functionland() {
            if($this->landVisible)
                return $this->land;
        }

        private bool $landVisible = false;
        public function landVisible() {
            return $this->landVisible;
        }
    }

    class Login {
        public function __construct(string $email, string $password) { 
            $this->email = $email;
            $this->password = $password;
        }

        private string $email = "";
        public function email() {
            return $this->email;
        }

        private string $password = "";
        public function password() {
            return $this->password;
        }
    }
?>