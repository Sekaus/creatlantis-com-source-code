<?php
    static $lastUpdateOnRulesAndPrivacy = "2025-11-16";

    include_once("./config.php");
    include_once("./data_handler.php");
    include_once("./user_classes.php");

    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

    // Load login FIRST
    $login = null;
    if (isset($_SESSION["user_login"]))
        $login = unserialize($_SESSION["user_login"]);

    // Setup the user's sessions
    if (!isset($_SESSION["user_data"]))
        $_SESSION["user_data"] = serialize(new User());

    $user = unserialize($_SESSION["user_data"]);

    $shouldShowPopup = $user->lastVersionOfReadAndAccept() !== $lastUpdateOnRulesAndPrivacy;
    $isNotANewUser = $user->lastVersionOfReadAndAccept() !== "";

    if (isset($_POST["agreed"]) && $_POST["agreed"] === "yes") {

        $user->setLastVersionOfReadAndAccept($lastUpdateOnRulesAndPrivacy);

        // NOW $login is properly set
        if ($login)
            $dh->updateUserInfo($user, $login);

        $_SESSION["user_data"] = serialize($user);
        exit;
    }
?>

<script type="module">
    import { ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        ChangeTheme("<?php echo $user->colorTheme(); ?>");
        
        // Show popup for the new user
        <?php if ($shouldShowPopup): ?>
            $(document).ready(() => {
                if(!location.pathname.includes("login"))
                    $("body").prepend(RulesAndPrivacyPopup(<?php echo ($isNotANewUser ? "true" : "false") ?>));
            });
        <?php endif; ?>

        $(document).on("submit", "#rules-privacy-form", function (event) {
            event.preventDefault();

            $.post(window.location.pathname, $(this).serialize(), () => {
                location.reload(); // refresh page so PHP sees updated session
            });
        });

        /* Navigation actions */
        $(document).ready(function () {
            $("#logout").click(function () {
                logout();
            });

            $(".theme-option").click(function () {
                switch($(this).attr('id')) {
                    case "dark-theme-option":
                        ChangeTheme(Themes.dark, true);
                        break;
                    case "light-theme-option":
                        ChangeTheme(Themes.light, true);
                        break;
                    case "green-theme-option":
                        ChangeTheme(Themes.green, true);
                        break;
                }
            });
        });

        function logout() {
            $.post("logout.php", function () {
                location.reload();
            });
        }

        // Show logout if the user is logged in, else show login
        <?php if (!isset($login)): ?>
            $("#login, #login:hidden").click(function() {
                window.location.href = "./login.php";
            });

            $("#logout, #logout:hidden").hide();
            $("#go-to-profile, #go-to-profile:hidden").hide();
            $("#go-to-profile, #go-to-profile:hidden").hide();
            $("#go-to-account-settings, #go-to-account-settings:hidden").hide()
            $("#points-balance, #points-balance:hidden").hide();
            $("#qick-submit, #qick-submit:hidden").hide()
        <?php else: ?>
            $("#login, #login:hidden").hide();
            
            var $qickSubmit = $("#qick-submit, #qick-submit:hidden");

            $qickSubmit.find("#submit-image").click(function() {
                window.location.href = window.location.origin + "/upload/image";
            });

            $qickSubmit.find("#submit-journal").click(function() {
                window.location.href = window.location.origin + "/upload/journal";
            });

            /* Change login info in the nav bar */

            $("#main-user-tap-metadata, #main-user-tap-metadata:hidden").find(".user-name").text("<?php echo $user->username(); ?>");

            var profileImage = "./images/default_pp.webp";

            <?php 
                if ($user->profileImage() !== null) 
                    echo 'profileImage = "' . $dh->GetURLOnSingleFile($user->profileImage()) . '";';
            ?>
            
            $("#main-user-tap-metadata, #main-user-tap-metadata:hidden").find(".user-icon").attr("src", profileImage);

            /* Navigation buttons */
            
            $("#go-to-profile, #go-to-profile:hidden").click(function() {
                window.location.href = window.location.origin + "/profile/<?php echo $user->username(); ?>";
            });

            $("#go-to-account-settings, #go-to-account-settings:hidden").click(function() {
                window.location.href = window.location.origin + "/account_settings.php";
            });

            $("#go-to-your-mailbox, #go-to-your-mailbox:hidden").click(function() {
                window.location.href = window.location.origin + "/inbox.php";
            });
        <?php endif; ?>
    });
</script>