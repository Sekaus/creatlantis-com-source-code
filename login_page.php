<!DOCTYPE html>
<html>
    <head>
        <?php include_once './header.php'; ?>
    </head>
    <body class="dark-theme">
        <div id="loaded-content">
            <form id='form-post' class='post-block' action="php_functions/mysql_functions/login_handler.php" method="POST" enctype="multipart/form-data">
                <h1>Log in:</h1>
                <fieldset id="login-page">
                    <label>Username:</label>
                    <input name="username_input" type="text" placeholder='Type in username...' required/>
                    <!--<input name="email" type="email" placeholder='example@mail.com...'/>!-->
                    <label>Password:</label>
                    <input name="password_input" type="password" placeholder='Type in password...' required/>

                    <button class="submit" type="submit" name="login_commando" value="login">Log in</button>
                </fieldset>
            </form>
        </div>
    </body>
</html>