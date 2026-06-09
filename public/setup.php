<?php
    const LAST_UPDATE_ON_RULES_AND_PRIVACY = "2026-05-26";
    const ANON_CONSENT_COOKIE = "rules_privacy_accept_version";

    include_once("./config.php");
    include_once("./data_handler.php");
    include_once("./user_classes.php");

    $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

    $unreadNoteCount = 0;

    // Load login FIRST
    $login = null;
    if (isset($_SESSION["user_login"]))
        $login = unserialize($_SESSION["user_login"]);

    // Setup the user's sessions
    if (!isset($_SESSION["user_data"]))
        $_SESSION["user_data"] = serialize(new User());

    $user = unserialize($_SESSION["user_data"]);

    // If the user is NOT logged in, use the cookie as the source of truth
    if (!$login && isset($_COOKIE[ANON_CONSENT_COOKIE])) {
        $user->setLastVersionOfReadAndAccept($_COOKIE[ANON_CONSENT_COOKIE]);
        $_SESSION["user_data"] = serialize($user);
    }

    $consentVersion = $login
        ? $user->lastVersionOfReadAndAccept()
        : ($_COOKIE[ANON_CONSENT_COOKIE] ?? "");

    $shouldShowPopup = $consentVersion !== LAST_UPDATE_ON_RULES_AND_PRIVACY;
    $isNotANewUser = $consentVersion !== "";

    if (isset($_POST["agreed"]) && $_POST["agreed"] === "yes") {

        $user->setLastVersionOfReadAndAccept($LAST_UPDATE_ON_RULES_AND_PRIVACY);

        if ($login) {
            $dh->updateUserInfo($user, $login);
        } else {

            $cookieExpiration = (new DateTime("+2 years"))->getTimestamp();

            setcookie(ANON_CONSENT_COOKIE, LAST_UPDATE_ON_RULES_AND_PRIVACY, [
                "expires"  => $cookieExpiration,
                "path"     => "/",
                "secure"   => !empty($_SERVER["HTTPS"]),
                "httponly" => true,
                "samesite" => "Lax",
            ]);

            // IMPORTANT:
            // Make the cookie available immediately in THIS request
            $_COOKIE[ANON_CONSENT_COOKIE] = $LAST_UPDATE_ON_RULES_AND_PRIVACY;
        }

        $_SESSION["user_data"] = serialize($user);

        exit;
    }

    if ($login) {
        $unreadNoteCount = $dh->countUnreadedInboxNotes($login, $user);
    }
?>

<script type="module">
    import { ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        $("#note-count").html("<?php echo $unreadNoteCount; ?>");

        ChangeTheme("<?php echo $user->colorTheme(); ?>");
        
        // Show popup for the new user
        <?php if ($shouldShowPopup): ?>
            if (
                !location.pathname.includes("login") &&
                !sessionStorage.getItem("rulesAccepted")
            ) {
                $("body").prepend(
                    RulesAndPrivacyPopup(<?php echo ($isNotANewUser ? "true" : "false") ?>)
                );
            }
        <?php endif; ?>

        $(document).on("submit", "#rules-privacy-form", function (event) {
            event.preventDefault();

            $.post(window.location.pathname, $(this).serialize(), () => {

                // HARD REMOVE popup immediately
                $("[data-rules-popup='true']").remove();

                // prevent any re-insertion flicker
                sessionStorage.setItem("rulesAccepted", "1");

                location.reload();
            });
        });

        /* Navigation actions */
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
            $("#go-to-account-settings, #go-to-account-settings:hidden").hide()
            $("#points-balance, #points-balance:hidden").hide();
            $("#qick-submit, #qick-submit:hidden").hide();
            $("#inbox-count, #inbox-count:hidden").hide();
            $("#note-count-container, #note-count-container:hidden").hide();
            $("#go-to-your-mailbox, #go-to-your-mailbox:hidden").hide();
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

        /* Profile links */
        $(document).on("click", ".profile-link", function() {
            event.preventDefault();

            var username = $(this).data("key");
            
            window.location.href = `${window.location.origin}/profile/${username}`;
        });
    });
</script>