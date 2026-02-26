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

        <div>
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
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Name:</label>
                        <div>
                            <input id="name-input" name="name" type="text" placeholder="Jack"
                                class="account-settings-input" />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Password:</label>
                        <div>
                            <input id="password-input" name="password" type="password" value="Not your real password"
                                class="account-settings-input" readOnly />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Email:</label>
                        <div>
                            <input id="email-input" name="email" type="email" placeholder="Jack@emil.com"
                                class="account-settings-input" />
                            <strong>This field is private</strong>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Land:</label>
                        <div>
                            <input id="land-input" name="land" type="text" placeholder="Germany"
                                class="account-settings-input" />
                            <b>Is this field is private?</b><input type="checkbox" id="land-visibility-checkbox" name="land-visibility"/>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Date of birth:</label>
                        <div>
                            <input id="date-of-birth-input" name="date-of-birth" type="text" placeholder="11/03/1998"
                                class="account-settings-input" />
                            <b>Is this field is private?</b><input type="checkbox" id="date-of-birth-visibility-checkbox" name="date-of-birth-visibility"/>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Gender:</label>
                        <div>
                            <input id="gender-input" name="gender" type="text" placeholder="Non-binary"
                                class="account-settings-input" />
                            <b>Is this field is private?</b><input type="checkbox" id="gender-visibility-checkbox" name="gender-visibility"/>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Username:</label>
                        <div>
                            <input id="username-input" name="username" type="text" placeholder="Goodie"
                                class="account-settings-input" />
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Tagline:</label>
                        <div>
                            <textarea id="tagline-input" name="tagline" cols="50" rows="1"
                                class="account-settings-input">So cool!</textarea>
                        </div>
                    </div>
                    <hr/>
                    <div class="account-settings-input-box">
                        <label>Hobbies:</label>
                        <div>
                            <textarea id="hobbies-input" name="hobbies" cols="50" rows="1"
                                class="account-settings-input">Gameing/Painting/Tagging</textarea>
                        </div>
                    </div>
                    <hr/>
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
        </div>

        <?php include_once("./html_elements/footer.html"); ?>
    </body>
    <script>
        /* Populate the form fields with the user's current data. */

        var user = {
            icon: '<?php echo $dh->GetURLOnSingleFile($user->profileImage()) ?? "../images/default_pp.webp"; ?>',
            name: '<?php echo $unfilteredData["name"] ?? ""; ?>',
            email: '<?php echo $login->email(); ?>',
            land: '<?php echo $unfilteredData["land"] ?? ""; ?>',
            landVisible: <?php echo $user->landVisible() ? "true" : "false"; ?>,
            dateOfBirth: '<?php echo $unfilteredData["dateOfBirth"] ?? ""; ?>',
            dateOfBirthVisible: <?php echo $user->dateOfBirthVisible() ? "true" : "false"; ?>,
            gender: '<?php echo $unfilteredData["gender"] ?? ""; ?>',
            genderVisible: <?php echo $user->genderVisible() ? "true" : "false"; ?>,
            username: '<?php echo $user->username(); ?>',
            tagline: '<?php echo $user->tagline(); ?>',
            hobbies: '<?php echo $user->hobbies(); ?>',
            bio: '<?php echo $user->biography(); ?>'
        }
        
        $(".user-icon").attr("src", user.icon);
        $("#name-input").val(user.name);
        $("#email-input").val(user.email);
        $("#land-input").val(user.land);
        $("#land-visibility-checkbox").prop("checked", user.landVisible);
        $("#date-of-birth-input").val(user.dateOfBirth);
        $("#date-of-birth-visibility-checkbox").prop("checked", user.dateOfBirthVisible);
        $("#gender-input").val(user.gender);
        $("#gender-visibility-checkbox").prop("checked", user.genderVisible);
        $("#username-input").val(user.username);
        $("#tagline-input").val(user.tagline);
        $("#hobbies-input").val(user.hobbies);
        $("#bio-input").val(user.bio);

        // When the cancel button is clicked, reset the form fields to their original values.
        $("#cancel").click(function() {
            // Reset the form fields to their original values.
            $("#name-input").val(user.name);
            $("#email-input").val(user.email);
            $("#land-input").val(user.land);
            $("#land-visibility-checkbox").prop("checked", user.landVisible);
            $("#date-of-birth-input").val(user.dateOfBirth);
            $("#date-of-birth-visibility-checkbox").prop("checked", user.dateOfBirthVisible);
            $("#gender-input").val(user.gender);
            $("#gender-visibility-checkbox").prop("checked", user.genderVisible);
            $("#username-input").val(user.username);
            $("#tagline-input").val(user.tagline);
            $("#hobbies-input").val(user.hobbies);
            $("#bio-input").val(user.bio);
        });

        $(".submit").click(function() {
            // Get the values from the form fields.
            var name = $("#name-input").val();
            var email = $("#email-input").val();
            var land = $("#land-input").val();
            var landVisible = $("#land-visibility-checkbox").is(":checked");
            var dateOfBirth = $("#date-of-birth-input").val();
            var dateOfBirthVisible = $("#date-of-birth-visibility-checkbox").is(":checked");
            var gender = $("#gender-input").val();
            var genderVisible = $("#gender-visibility-checkbox").is(":checked");
            var username = $("#username-input").val();
            var tagline = $("#tagline-input").val();
            var hobbies = $("#hobbies-input").val();
            var bio = $("#bio-input").val();

            // Send the updated data to the server using AJAX.
            $.ajax({
                url: "settings_handler.php",
                method: "POST",
                data: {
                    command: "update_profile",
                    name: name,
                    email: email,
                    land: land,
                    landVisible: landVisible,
                    dateOfBirth: dateOfBirth,
                    dateOfBirthVisible: dateOfBirthVisible,
                    gender: gender,
                    genderVisible: genderVisible,
                    username: username,
                    tagline: tagline,
                    hobbies: hobbies,
                    bio: bio
                },
                success: function(response) {
                    if(response.success) {
                        alert("Profile updated successfully!");
                        location.reload(); // Reload the page to reflect the changes.
                    } else {
                        alert("Error updating profile: " + response.error);
                    }
                },
                error: function() {
                    alert("An error occurred while updating your profile. Please try again later.");
                }
            });
        });
    </script>
</html>