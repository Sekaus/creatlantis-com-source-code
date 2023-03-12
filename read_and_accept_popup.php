<!-- read and accept popup start !-->
<?php
    /* logic to the read and accept popup */

    // check if user has given an answer
    if(isset($_POST['had-read-terms-of-service']) && isset($_POST['had-read-privacy-policy']))
        if($_POST['had-read-terms-of-service'] == 'yes' && $_POST['had-read-privacy-policy'] == 'yes')
            HasReadAndAcceptPopup();
?>
<?php if(readAndAcceptPopup() != 'has_read_and_accept') { ?>
<div id="read-and-accept-backgrund">
    
</div>

<div id="read-and-accept-box">
        <h1>Before you use the site!</h1>
        <h2>You must go through and agree with the rules of our site and have read and accept our Terms of Service and Privacy Policy.</h2>
        <hr/>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <strong>I have read and agree to the Terms of Service and rules for using this website.</strong> <input name="had-read-terms-of-service" type="checkbox" value="yes" required/> <small>Read the Terms of Service and the rules here:<a href="./html_documents/terms_of_use.html">Terms of Service</a></small><br/>
            <strong>I have read and agree to the Privacy Policy for using this website.</strong> <input name="had-read-privacy-policy" type="checkbox" value="yes" required/> <small>Read the Privacy Policy here:<a href="./html_documents/private_policy.html">Privacy Policy</a></small><br/>
            <button name="read_and_agree" class="submit" type="submit">OK</button>
        </form>
</div>

<?php } ?>

<!-- read and accept popup end !-->