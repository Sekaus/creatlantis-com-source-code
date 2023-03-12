<!DOCTYPE html>
<html>
    <head>
        <?php include_once './header.php'; ?>
    </head>
    <body class="dark-theme">
        <div id="loaded-content">
            <form id='form-post' class='post-block' action="php_functions/mysql_functions/login_handler.php" method="POST" enctype="multipart/form-data">
                <fieldset id="login-page">
                    <input name="username_input" type="text" placeholder='User name...' required/>
                    <!--<input name="email" type="email" placeholder='example@mail.com...'/>!-->
                    <input name="password_input" type="password" placeholder='Password...' required/>

                    <button class="submit" type="submit" name="login_commando" value="login">Login</button>
                </fieldset>
            </form>
        </div>
    </body>
</html>