<!DOCTYPE html>
<html>
  <head>
    <title>Main</title>
    <?php include_once("./html_elements/head.html"); ?>
  </head>
  <body>
    <?php include_once("./setup.php"); ?>
    <?php include_once("./html_elements/navigation_bar.html"); ?>

    <ol id="upload-nav-taps" alt="Upload nav taps">
        <!-- Uploading files here -->
    </ol>

    <form id="upload-new-post" enctype="multipart/form-data" method="POST">
        <br/>
        <div id="upload-top">
            <p>Post type: </p>
            <select id="submit-options" name="post-type" class="upload-input" required>
                <option value="" disabled selected>Select a post type</option>
                <option value="image">Image</option>
                <option value="journal">Journal</option>
            </select>
        </div>

        <br/>

        <div id="upload-bottom">
          <p>Title:</p>
          <input id="file-name" type="text" name="title" placeholder="title..." class="upload-input" required/>

          <div id="no-type-selected" class="upload-part">
            <p class="extra-big-text" class="upload-input">Please select a file type to continue.</p>
          </div>

          <div id="image-file-upload-part" class="upload-part">
            <p id="image-file-input-title" class="big-text">Drop a image here, or click to upload.</p>
            <input id="file-input-button" type="file" name="image" accept="image/*" class="upload-input" required/>
            <img id="image-preview" src="" class="post"/>
          </div>

          <div id="journal-submit-part" class="upload-part">
            <p>Body:</p>
            <textarea id="journal-body" name="body" class="upload-input post journal-content" required></textarea>
          </div>

          <br/>

          <p>Tags:</p>
          <input name="tags" size="50" placeholder="#tags..." class="upload-input">
        </div>

        <br/>

        <div id="upload-post-icons">
            <div class="vertical-hr"></div>

            <button id="cancel" type="reset" disabled>Cancel</button>
            <button type="submit "class="submit" disabled>Submit</button>

            <div class="vertical-hr"></div>
        </div>
    </form>

    <br/>

    <script>
        function queueUploadingFile(selected) {
          var html = /*html*/ `
            <li class="${"upload-tap" + (selected ? " selected-upload-tap" : "")}">
              <a>File name</a>
              <p title="Cancel upload of this file" class="cancel-upload"><b>X</b></p>
            </li>
          `;

          $("#upload-nav-taps").append(html);
        }

        $(document).ready(function() {
          // Initialize: Hide the specific parts we don't want visible at first load
          $(".upload-part").hide();

          // Ensure "no-type-selected" is shown initially
          $("#no-type-selected").show(); 

          $("#submit-options").on("change", function() {
            // Hide all parts first
            $(".upload-part").hide();

            // Show the correct part based on the selected value
            switch($(this).val()) {
              case "image":
                $("#image-file-upload-part").show();
                $("#image-file-input-title").show();
                $("#image-file-upload-part #file-input-button").show();
                break;
            case "journal":
                $("#journal-submit-part").show();
                break;
            }
          });

          // Change the file display
          $("#image-file-upload-part #file-input-button").on("change", function() {
            readURL(this);
            $(this).hide();
            $("#upload-post-icons button").prop('disabled', false);
          });

          // UnLock post submit and cancel button for journals
          $("#journal-body").on("input change", function() {
            if($("#journal-body").val() !== "")
              $("#upload-post-icons button").prop('disabled', false);
            else
              $("#upload-post-icons button").prop('disabled', true);
          });

          // Cancel or submit the uploading post from user input
          if ($('#file-button').attr('disabled') !== "disabled") {
            $("#upload-post-icons #cancel").click(function(event) {
              event.preventDefault(); 

              $("#upload-new-post")[0].reset(); 
              $("#image-preview").prop("src", '').hide();
              $(".upload-part").hide();
              $("#no-type-selected").show();
              $("#upload-post-icons button").prop('disabled', true);
            });
            $("#upload-post-icons .submit").click(function(event) {
              
            });
          }
        });

        function readURL(input) {
          if (input.files && input.files[0]) {
              var reader = new FileReader();
              var file = input.files[0];

              reader.onload = function (e) {
                  $("#image-preview").attr("src", e.target.result).show();
                  $("#image-file-input-title").hide();
                  $("#file-name").val(file.name);
              }

              reader.readAsDataURL(input.files[0]);
          }
        }
    </script>

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>

<?php
  global $dbConfig;
  global $s3Config;
  global $user;

  include_once("./data_handler.php");

  $dh = new DataHandle($dbConfig, $s3Config, S3BotType::writeOnly);
?>