<?php
    declare(strict_types=1);

    include_once("./config.php");
    include_once("./data_handler.php");

    class User {
        // Declare properties up front (with sensible defaults)
        private string $uuid = "";
        private string $username = "Guest";
        private string $tagline = "";
        private string $biography = "";
        private string $dateOfBirth = "";
        private bool $dateOfBirthVisible = false;
        private string $gender = "";
        private bool $genderVisible = false;
        private string $registrationDate = "";
        private ?string $profileImage = null;
        private string $lastVersionOfReadAndAccept = "";
        private string $colorTheme = "dark";
        private string $land = "";
        private bool $landVisible = false;
        private string $hobbies = "";
        private ?string $name = null;

        // Constructor - accepts an associative row (from DB) or null
        public function __construct(?array $dataRow = null) {
            if ($dataRow !== null) {
                // Use null-coalescing to avoid notices when keys are missing
                $this->uuid = $dataRow["uuid"] ?? $this->uuid;
                $this->username = $dataRow["username"] ?? $this->username;
                $this->tagline = $dataRow["tagline"] ?? $this->tagline;
                $this->biography = $dataRow["bio"] ?? $this->biography;
                $this->dateOfBirth = $dataRow["date_of_birth"] ?? $this->dateOfBirth;
                $this->dateOfBirthVisible = (bool)($dataRow["date_of_birth_visible"] ?? $this->dateOfBirthVisible);
                $this->gender = $dataRow["gender"] ?? $this->gender;
                $this->genderVisible = (bool)($dataRow["gender_visible"] ?? $this->genderVisible);
                $this->registrationDate = $dataRow["registration_date"] ?? $this->registrationDate;
                $this->profileImage = $dataRow["profile_image"] ?? $this->profileImage;
                $this->colorTheme = $dataRow["color_theme"] ?? $this->colorTheme;
                $this->land = $dataRow["land"] ?? $this->land;
                $this->landVisible = (bool)($dataRow["land_visible"] ?? $this->landVisible);
                $this->lastVersionOfReadAndAccept = $dataRow["last_version_of_read_and_accept"] ?? '';
                $this->hobbies = $dataRow["hobbies"] ?? $this->hobbies;
            }
        }

        /**
         * Return more sensitive fields only when the login credentials are verified.
         * Note: prefer injecting DataHandle instead of constructing it here.
         */
        public function unfilteredData(Login $login): ?array {
            global $dbConfig, $s3Config;

            // Pass appropriate S3BotType (readOnly) if your DataHandle requires it.
            // If DataHandle constructor requires 3 args, include S3BotType::readOnly.
            $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

            if ($dh->verifyOwnership($login->email(), $login->password(), $this->username)) {
                return [
                    'dateOfBirth' => $this->dateOfBirth,
                    'gender'      => $this->gender,
                    'land'        => $this->land,
                    'name'        => $this->name
                ];
            }

            return null;
        }

        // ----- Getters & Setters -----
        public function uuid(): string { return $this->uuid; }
        public function username(): string { return $this->username; }
        public function tagline(): string { return $this->tagline; }
        public function biography(): string { return $this->biography; }

        // Date of birth getter with visibility control
        public function dateOfBirth(): string {
            return $this->dateOfBirthVisible ? $this->dateOfBirth : "Not public";
        }
        public function dateOfBirthVisible(): bool { return $this->dateOfBirthVisible; }

        public function gender(): string {
            return $this->genderVisible ? $this->gender : "Not public";
        }
        public function genderVisible(): bool { return $this->genderVisible; }

        public function registrationDate(): string { return $this->registrationDate; }

        /**
         * profileImage($asKey = true)
         * - if $asKey === true returns "<uuid>/<filename>" or null
         * - if $asKey === false returns raw stored filename or null
         */
        public function profileImage(bool $asKey = true): ?string {
            if ($asKey) {
                if ($this->profileImage === null || $this->profileImage === "") return null;
                return $this->uuid . "/" . $this->profileImage;
            }
            return $this->profileImage;
        }

        public function lastVersionOfReadAndAccept(): string { return $this->lastVersionOfReadAndAccept; }
        public function setLastVersionOfReadAndAccept(string $v): void { $this->lastVersionOfReadAndAccept = $v; }

        public function colorTheme(): string { return $this->colorTheme; }
        public function setColorTheme(string $c): void { $this->colorTheme = $c; }

        public function land(): string { return $this->landVisible ? $this->land : "Not public"; }
        public function landVisible(): bool { return $this->landVisible; }

        public function hobbies(): string { return $this->hobbies; }
        public function setHobbies(string $h): void { $this->hobbies = $h; }
    }

    class Login {
        private string $email = "";
        private string $password = "";
        private string $name = "";

        public function __construct(string $email, string $password, string $name = "") {
            $this->email = $email;
            $this->password = $password;
            $this->name = $name;
        }

        public function email(): string { return $this->email; }
        public function password(): string { return $this->password; }
        public function name(): string { return $this->name; }
    }
?>