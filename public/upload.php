<?php
  session_start();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Upload</title>
    <?php include_once("./html_elements/head.html"); ?>
  </head>
  <body>
    <?php include_once("./html_elements/navigation_bar.php"); ?>
    <?php include_once("./setup.php"); ?>

    <ol id="upload-nav-taps" alt="Upload nav taps">
        <!-- Uploading files here -->
    </ol>

    <form id="upload-new-post" enctype="multipart/form-data" method="POST">
        <br/>
        <div id="upload-top">
            <p>Post type: </p>
            <select id="submit-options" name="post_type" class="upload-input" required>
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
        
        <div id="progress">
          Uploaded <var class="upload-percent">-1</var>% of the post...
        </div>

        <br/>

        <div id="upload-post-icons">
            <div class="vertical-hr"></div>

            <button id="cancel" type="reset" disabled>Cancel</button>
            <button type="submit" class="submit" disabled>Submit</button>

            <div class="vertical-hr"></div>
        </div>
    </form>

    <br/>

    <script>
      // Only show this page if the user had logged in
      if(<?php echo isset($login) ? "false" : "true"; ?>)
        window.location.replace("./login.php");

      $(document).ready(function() {

      // Initial UI state
      $(".upload-part").hide();
      $("#no-type-selected").show();
      $("#upload-post-icons button").prop('disabled', true);
      $("#progress").hide();

      // Handle post type selection
      $("#submit-options").on("change", function() {
        $(".upload-part").hide();

        // Disable requirements first
        $("#journal-body").prop("required", false);
        $("#file-input-button").prop("required", false);

        switch ($(this).val()) {
            case "image":
                $("#image-file-upload-part").show();
                $("#image-file-input-title").show();
                $("#file-input-button").show().prop("required", true);
                break;

            case "journal":
                $("#journal-submit-part").show();
                $("#journal-body").prop("required", true);
                break;
        }
      });

      // Enable buttons when an image is selected
      $("#file-input-button").on("change", function() {
          readURL(this);
          $(this).hide();
          $("#upload-post-icons button").prop('disabled', false);
      });

      // Enable buttons when journal text is typed
      $("#journal-body").on("input", function() {
          $("#upload-post-icons button").prop(
              'disabled',
              $(this).val().trim() === ""
          );
      });

      // Reset form
      $("#upload-post-icons #cancel").click(function(event) {
          $("#upload-new-post")[0].reset();
          $("#image-preview").prop("src", "").hide();
          $(".upload-part").hide();
          $("#no-type-selected").show();
          $("#upload-post-icons button").prop('disabled', true);
          $("#file-input-button").show();
      });

      $("#upload-new-post").on("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);

        // Submit form via AJAX (NO PROGRESS BAR)
        $.ajax({
          url: "./file_upload_handel.php",
          method: "POST",
          data: formData,
          contentType: false,
          processData: false,

          xhr: function() {
              $("#progress").show();
              $(".upload-percent").text(0);

              let xhr = new window.XMLHttpRequest();

              xhr.upload.addEventListener("progress", function(e) {
                  if (e.lengthComputable) {
                      let percent = Math.round((e.loaded / e.total) * 100);
                      $(".upload-percent").text(percent);
                  }
              });

              $("#upload-post-icons button").prop('disabled', true);

              return xhr;
          },

          success: function(response) {
              alert("Upload Complete!");
              window.location.href = "index.php";
          },
          error: function(xhr) {
              alert("Upload failed: " + JSON.parse(xhr.responseText).error);
          }
        });
      });
    });

    // Preview function
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var file = input.files[0];

            reader.onload = function(e) {
                $("#image-preview").attr("src", e.target.result).show();
                $("#image-file-input-title").hide();
                $("#file-name").val(file.name);
            };

            reader.readAsDataURL(file);
        }
      }
    </script>

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>