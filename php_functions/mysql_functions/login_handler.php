    <?php
    session_start();
    
    include_once 'db_connect.php';
    
    if(isset($_POST['login_commando'])) {
        //logout
        if($_POST['login_commando'] == "logout") {
            //reset session
            session_destroy();
            header("Location: ../../login_page.php");
        }

        //login
        else if($_POST['login_commando'] == "login") {
            //verify login input
            if(verifyLogin($_POST['username_input'], $_POST['password_input'])) {
                header("Location: ../../index.php");
            }
            else
                header("Location: ../../login_page.php");
        }
        
        else
            header("Location: ../../login_page.php");
    }
    
    //verify login input
    function verifyLogin($username_input, $password_input) {
        global $mysqli;

        // prepare an select statement of user_info
        $stmt = $mysqli->prepare("SELECT uuid, username, tagline, email, date_of_birth, gender FROM user_info WHERE username=? AND password=PASSWORD(?)");
        // bind variables to the prepared statement as parameters to user_info and get input variables from login form POST
        $stmt->bind_param("ss", $username_input, $password_input);
        $stmt->execute();

        // bind result variables
        $stmt->bind_result($UUID, $USERNAME, $TAGLINE, $EMAIL, $DATE_OF_BIRTH, $GENDER);

        // fetch values
        while ($stmt->fetch()) {
            // Set login session
            $_SESSION['uuid'] = $UUID;
            $_SESSION['username'] = $USERNAME;
            $_SESSION['tagline'] = $TAGLINE;
            $_SESSION['email'] = $EMAIL;
            $_SESSION['date_of_birth'] = $DATE_OF_BIRTH;
            $_SESSION['gender'] = $GENDER;
            $_SESSION['password'] = $password_input;
        }
        
        $stmt->close();
        
        // test if login verify is success
        if(isset($_SESSION["uuid"]) && $_SESSION['uuid'] != "")
            return true;
        else
            return false;
    }
    
    // check the ownership of a profile by getting the password and uuid
    function checkLoginOwnership($uuid, $password) {
        global $mysqli;

        // prepare an select statement of user_info
        $stmt = $mysqli->prepare("SELECT uuid FROM user_info WHERE uuid=? AND password=PASSWORD(?)");
        // bind variables to the prepared statement as parameters to user_info and get input variables from login form POST
        $stmt->bind_param("ss", $uuid, $password);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // test if user by uuid has ownership of the login
        if(mysqli_num_rows($result) > 0)
            return true;
        else
            return false;
        
        $stmt->close();
    }
?>