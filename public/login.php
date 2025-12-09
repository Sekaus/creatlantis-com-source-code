<?php
  session_start();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Main</title>
    <?php include_once("./html_elements/head.html"); ?>
  </head>
  <body>
    <?php include_once("./setup.php"); ?>

    <div id="login-form-box">
        <form id="login-form" enctype="multipart/form-data" method="POST">
            <div id="login-box">
                <h1 class="extra-big-text">Login</h1>

                <br/>

                <div id="login-input-box">
                    <input type="email" name="email" placeholder="email" class="login-input"/>

                    <br/>

                    <input type="password" name="password" placeholder="password" class="login-input"/>
                    
                    <br/>
                  </div>
                  <br/>

                  <div id="login-submit-box">
                    <div class="vertical-hr"></div>

                    <input type="submit" class="submit" />

                    <div class="vertical-hr"></div>
                  </div>

                  <br/>
                  
                  <p>
                    Can't log in, or have you forgotten your password?
                    <br/>
                    No problem. Just send a text message to one of the admins on our official Discord server!
                    <br/>
                    <a href="https://discord.gg/KUehpdtvvQ">https://discord.gg/KUehpdtvvQ</a>
                  </p>
                </div>
            </form>
        </div>
        
        <script>
          $(document).ready(function(){
            $("#login-form").on("submit", function(event) {
              event.preventDefault();

              let formData = new FormData(this);

              // Submit form via AJAX (NO PROGRESS BAR)
              $.ajax({
                url: "./login_handel.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                
                success: function(response) {
                    if (response.success) {
                        alert("Login Complete!");
                        window.location.href = "index.php";
                    } else {
                        console.log("Login failed: " + response);
                    }
                },
                error: function(xhr) {
                    let msg = "Login failed";

                    try {
                        let json = JSON.parse(xhr.responseText);
                        if (json.error) msg = json.error;
                    } catch (e) {
                        // Not JSON → show raw response for debugging
                        console.error("Non-JSON error response:", xhr.responseText);
                        msg = "Server error. Check console.";
                    }

                    alert(msg);
                }
              });
            });
          });
        </script>
  </body>
</html>