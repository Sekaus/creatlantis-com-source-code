<?php
    session_start();

    // Setup the user's sessions
    if(!isset($_SESSION["user_data"])) 
        $newUser = new User();
?>

<script type="module">
    import { Startup, ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        Startup();
        ChangeTheme(Themes.dark);
        $("body").prepend(RulesAndPrivacyPopup());
    });
</script>