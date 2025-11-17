<?php
    session_start();

    include_once("./user_classes.php");

    // Setup the user's sessions
    if(!isset($_SESSION["user_data"])) {
        $guest = new User();
        $_SESSION["user_data"] = serialize($guest);
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