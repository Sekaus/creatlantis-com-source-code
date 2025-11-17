<?php
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
?>

<script type="module">
    import { Startup, ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        Startup();
        ChangeTheme(Themes.dark);
        $("body").prepend(RulesAndPrivacyPopup());
    });
</script>