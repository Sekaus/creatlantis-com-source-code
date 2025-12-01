<?php
    static $lastUpdateOnRulesAndPrivacy = "2025-11-16";

    include_once("./config.php");
    include_once("./data_handler.php");
    include_once("./user_classes.php");

    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

    // Setup the user's sessions
    if (!isset($_SESSION["user_data"]))
        $_SESSION["user_data"] = serialize(new User());

    $user = unserialize($_SESSION["user_data"]);
    
    $shouldShowPopup = $user->lastVersionOfReadAndAccept() !== $lastUpdateOnRulesAndPrivacy;
    $isNotANewUser = $user->lastVersionOfReadAndAccept() !== "";

    if (isset($_POST["agreed"]) && $_POST["agreed"] === "yes") {
        global $lastUpdateOnRulesAndPrivacy;

        $user->setLastVersionOfReadAndAccept($lastUpdateOnRulesAndPrivacy);
        $_SESSION["user_data"] = serialize($user);

        exit;
    }

    $login = null;
    if (isset($_SESSION["user_login"]))
        $login = unserialize($_SESSION["user_login"]);
?>

<script type="module">
    import { ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        ChangeTheme(Themes.dark);
        
        // Show popup for the new user
        <?php if ($shouldShowPopup): ?>
            $(document).ready(() => {
                $("body").prepend(RulesAndPrivacyPopup(<?php echo ($isNotANewUser ? "true" : "false") ?>));
            });
        <?php endif; ?>

        $(document).on("submit", "#rules-privacy-form", function (event) {
            event.preventDefault();

            $.post(window.location.pathname, $(this).serialize(), () => {
                location.reload(); // refresh page so PHP sees updated session
            });
        });

        // Show logout if the user is logged in, else show login
        <?php if (!isset($login)): ?>
            $("#logout").hide();
            $("#login").show();
        <?php else: ?>
            $("#login").hide();
            $("#logout").show();
        <?php endif;?>

        // Change login info in the nav bar
        $("#main-user .user-name").text("<?php echo $user->username(); ?>");

        var profileImage = "./images/default_pp.webp";

        <?php 
            if ($user->profileImage() !== null) 
                echo 'profileImage = "' . $dh->GetURLOnSingleFile($user->profileImage()) . '";';
        ?>
        
        $("#main-user .user-icon").attr("src", profileImage);
    });
</script>