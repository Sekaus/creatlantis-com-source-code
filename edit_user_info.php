<?php
    include_once 'php_functions/mysql_functions/user_info_actions.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>New Web Side Demo!</title>
        <?php include_once './header.php' ?>
    </head>
    <body>
        <?php include_once './nav_bar.php'; ?>
        
        <!-- user info display start !-->
        <!-- TO-DO: make me clean !-->
        <div id="loaded-content">
            <div id="edit-profile-info">
                <dl class="post-block">
                    <dt>User details:</dt>
                    <dd>
                        <form class="user-index-0" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST">
                            <dl class="user-details second-user post-block user-text-info">
                                <dt>Profile image:</dt>
                                <dd><img class="profile-image" src="images/pfp.png"/></dd>
                                <a class="edit" href="submit.php?post=profile_image">Edit</a>

                                <dt>User name:</dt>
                                <dd class="user-name"></dd>     <dd><input class="user-name" name="new_username" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_username">Edit</button></dd>

                                <dt>Tagline:</dt>
                                <dd class="user-tagline"></dd>      <dd><input class="user-tagline" name="new_tagline" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_tagline">Edit</button></dd>
                                
                                <dt>BIO:</dt>
                                <dd class="user-bio"></dd>      <dd><textarea cols="30" rows="20" class="user-bio" name="new_bio" hidden></textarea></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_bio">Edit</button></dd>
                                
                                <dt>Date of birth:</dt>
                                <dd class="date-of-birth"></dd>      <dd><input class="date-of-birth" name="new_date_of_birth" type="date" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_date_of_birth">Edit</button></dd>

                                <dt>Gender:</dt>
                                <dd class="gender"></dd>        <dd><input class="gender" name="new_gender" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_gender">Edit</button></dd>
                            </dl>
                            <button class="submit" name="user_info_command" type="submit" value="update_user_profile_info">Save</button>
                        </form>
                    </dd>
                    
                    <dt>Login info:</dt>
                    <dd>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" method="POST">
                            <dl id="user-login-info" class="post-block">
                                <dt>Email:</dt>
                                <dd class="email"></dd>      <dd><input class="email" name="new_email" type="email" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_email">Edit</button></dd>

                                <dt>Password:</dt>
                                <dd class="password"></dd>      <dd><input class="password" name="new_password" type="password" hidden/></dd>
                                <dd><button class="edit edit-user-info" name="user_info_command" type="button" value="edit_password">Edit</button></dd>
                            </dl>

                            <label>Please enter your password to perform the changes to your login:</label>
                            <input name="old_password" type="password" placeholder="type in here..." required="required"/>

                            <button class="submit" name="user_info_command" type="submit" value="update_user_login_info">Save</button>
                        </form>
                    </dd>
                </dl>
            </div>
        </div>
        
        <!-- user info display end !-->
        
        <?php include_once './footer.php';?>

        <!-- JQuery content !-->
        <script type='text/javascript'>
            // hide search filters
            hideSearchFilters = true;
            
            // display user id
            <?php
                loadUserInfo('#edit-profile-info', $_SESSION['uuid'], 'true');
                loadUserLoin();
            ?>
            
            // setup input field to start edit user infor
            $('.edit-user-info').click(function(){
                var editUserCommand = $(this).val();
                var inputType;
                var targetInput;
                
                // do setup based on the user info command value
                switch(editUserCommand) {
                    case 'edit_username':
                        targetInput = '.user-name';
                        break;
                    case 'edit_tagline':
                        targetInput = '.user-tagline';
                        break;
                    case 'edit_bio':
                        targetInput = '.user-bio';
                        inputType = 'textarea';
                        break;
                    case 'edit_date_of_birth':
                        targetInput = '.date-of-birth';
                        break;
                    case 'edit_gender':
                        targetInput = '.gender';
                        break;
                    case 'edit_email':
                        targetInput = '.email';
                        break;
                    case 'edit_password':
                        targetInput = '.password';
                        break;
                }
                $('#edit-profile-info ' + targetInput).toggle();
                $(this).text(($(this).text() === 'Edit' ? 'Cancel' : 'Edit'));
                autoFillInput(targetInput, inputType);
            });
            
            // autofill inputs fields with the user's info and login data
            $(document).ready( function() {
                autoFillInput('.user-name');
                autoFillInput('.user-tagline');
                autoFillInput('.user-bio', 'textarea');
                autoFillInput('.date-of-birth');
                autoFillInput('.gender');
                autoFillInput('.email');
                autoFillInput('.user-name');
            });
            
            // autofill a single input field with some user data
            function autoFillInput(targetInput, inputType = 'input') {
                $(inputType + targetInput).val($('dd' + targetInput).text());
            }
        </script>
    </body>
</html>