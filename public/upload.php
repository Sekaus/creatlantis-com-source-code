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
        <div id="upload-top">
            <label>Post type: </label>
            <select id="submit-options" name="post-type" class="upload-input" required>
                <option value="" disabled selected>Select a post type</option>
                <option value="image">Image</option>
                <option value="blog">Blog</option>
            </select>
        </div>

        <br/>

        <div id="upload-bottom">
            <p class="big-text">Drop a image here, or click to upload.</p>
            <input id="file-input-button" type="file" name="image" accept="image/*" class="upload-input" required/>
        </div>

        <br/>

        <div id="upload-post-icons">
            <div class="vertical-hr"></div>

            <button id="cancel">Cancel</button>
            <input type="submit" value="Submit" class="submit"/>

            <div class="vertical-hr"></div>
        </div>

    </form>

    <br/>

    <script>
        function UploadingFile(selected) {
        return /*html*/ `
            <li class="${"upload-tap" + (selected ? " selected-upload-tap" : "")}">
              <a>File name</a>
              <p title="Cancel upload of this file" class="cancel-upload"><b>X</b></p>
            </li>
          `;
      }
    </script>

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>