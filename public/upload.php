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
          <input type="text" name="title" placeholder="title..." class="upload-input"/>
          <div id="no-type-selected" hidden>
            <p class="extra-big-text" class="upload-input">Please select a file type to continue.</p>
          </div>
          <div id="file-upload-part" hidden>
            <p class="big-text">Drop a image here, or click to upload.</p>
            <input id="file-input-button" type="file" name="image" accept="image/*" class="upload-input" required/>
          </div>
          <div id="journal-submit-part">
            <p>Body:</p>
            <textarea name="body" class="upload-input post journal-content"></textarea>
          </div>
        </div>

        <br/>

        <div id="upload-post-icons">
            <div class="vertical-hr"></div>

            <button id="cancel" disabled>Cancel</button>
            <input type="submit" value="Submit" class="submit" disabled/>

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
    </script>

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>