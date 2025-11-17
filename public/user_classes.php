<?php
    class User {
        private $uuid = "";
        private $username = "guest";
        private $tagline = "";
        private $biography = "";
        private $dateOfBirth = "";
        private $dateOfBirthVisible = false;
        private $gender = "";
        private $genderVisible = false;
        private $registrationDate = "";
        private $profileImage = "./images/default_pp.webp";
        private $colorTheme = "dark";
        private $land = "";
        private $landVisible = false;

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
    }

    class Login {
        private $email = "";
        private $password = "";
    }
?>