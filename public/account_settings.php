<?php
    require_once("./user_classes.php");
    require_once("./config.php");
    require_once("./data_handler.php");

    $dh = new DataHandle($dbConfig, $s3Config);

    $user = unserialize($_SESSION["user_data"]);
    $login = unserialize($_SESSION["user_login"]);
    $unfilteredData = $user->unfilteredData($login);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Account Settings</title>
        <?php include_once("./html_elements/head.html"); ?>
    </head>
    <body>
        <?php include_once("./setup.php"); ?>
        <?php include_once("./navigation_bar.php"); ?>

        <form action="" method="POST">
            <div id="account-settings">
                <div id="account-settings-box">
                    <div class="account-settings-input-box">
                        <label>Profile picture:</label>
                        <div>
                            <img class="user-icon" src="../images/default_pp.webp" />
                            <input id="profile-picture-input" name="profile-picture" type="file" accept="image/*"
                                class="account-settings-input" />
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Name:</label>
                        <div>
                            <input id="name-input" name="name" type="text" placeholder="Jack"
                                class="account-settings-input" />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Password:</label>
                        <div>
                            <input id="password-input" name="password" type="password" value="Not your real password"
                                class="account-settings-input" readOnly />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Email:</label>
                        <div>
                            <input id="email-input" name="email" type="email" placeholder="Jack@emil.com"
                                class="account-settings-input" />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Date of birth:</label>
                        <div>
                            <input id="date-of-birth-input" name="date-of-birth" type="text" placeholder="11/03/1998"
                                class="account-settings-input" />
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Username:</label>
                        <div>
                            <input id="username-input" name="username" type="text" placeholder="Goodie"
                                class="account-settings-input" />
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Tagline:</label>
                        <div>
                            <textarea id="tagline-input" name="tagline" cols="50" rows="1"
                                class="account-settings-input">So cool!</textarea>
                        </div>
                    </div>
                    <hr />
                    <div class="account-settings-input-box">
                        <label>Hobbies:</label>
                        <div>
                            <textarea id="hobbies-input" name="hobbies" cols="50" rows="1"
                                class="account-settings-input">Gameing/Painting/Tagging</textarea>
                        </div>
                    </div>
                    <div class="account-settings-input-box">
                        <label>BIO:</label>
                        <div>
                            <textarea id="bio-input" name="bio" cols="50" rows="5"
                                class="account-settings-input">So cool!</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div id="update-profile-icons">
                <div class="vertical-hr"></div>

                <button id="cancel">Cancel</button>
                <input type="submit" value="Submit" class="submit" />

                <div class="vertical-hr"></div>
            </div>
        </form>

        <?php include_once("./html_elements/footer.html"); ?>
    </body>
    <script>
        /* Populate the form fields with the user's current data. */

        var user = {
            icon: '<?php echo $dh->GetURLOnSingleFile($user->profileImage()) ?? "../images/default_pp.webp"; ?>',
            name: '<?php echo $unfilteredData["name"] ?? ""; ?>',
            email: '<?php echo $login->email(); ?>',
            dateOfBirth: '<?php echo $unfilteredData["dateOfBirth"] ?? ""; ?>',
            username: '<?php echo $user->username(); ?>',
            dateOfBirth: '<?php echo $user->dateOfBirth(); ?>',
            tagline: '<?php echo $user->tagline(); ?>',
            hobbies: '<?php echo $user->hobbies(); ?>',
            bio: '<?php echo $user->biography(); ?>'
        }
        
        $(".user-icon").attr("src", user.icon);
        $("#name-input").val(user.name);
        $("#email-input").val(user.email);
        $("#date-of-birth-input").val(user.dateOfBirth);
        $("#username-input").val(user.username);
        $("#tagline-input").val(user.tagline);
        $("#hobbies-input").val(user.hobbies);
        $("#bio-input").val(user.bio);
    </script>
</html>