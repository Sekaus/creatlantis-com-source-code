<!DOCTYPE html>
<html>
  <head>
    <title>Main</title>
    <?php include_once("./html_elements/head.html"); ?>
  </head>
  <body>
    <?php include_once("./setup.php"); ?>
    <?php include_once("./html_elements/navigation_bar.html"); ?>

    <div id="index-box">
      <ol id="category-list">
        <li className="selected-category">All</li>
        <li className="">Drawings</li>
        <li className="">Paintings</li>
        <li className="">Other</li>
      </ol>
      <div id="content-view">
        <!-- Post links here -->
      </div>
    </div>

    <script type="module">
      function Folder() {
        return (
          <>
            <li class="folder">
              <img src={defaultImg} class="folder-thumbnail" />
              <p class="folder-name">Name</p>
              <div class="folder-icon-container">
                <img src={folderContentIcon} class="folder-icon" />
                <var title="The number of items in this folder" class="folder-content-count">-999</var>
              </div>
            </li>
          </>
        )
      }

      function PostReaction(emoji) {
        return (
          <>
            <div class="post-reaction">
              <var title="The count of this reaction" class="reaction-count">-5</var>
              <p title="Add this reaction to this post" class="reaction-emoji">{emoji}</p>
            </div>
          </>
        )
      }

      function UploadingFile(selected) {
        return (
          <>
            <li class={"upload-tap" + (selected ? " selected-upload-tap" : "")}>
              <a>File name</a>
              <p title="Cancel upload of this file" class="cancel-upload"><b>X</b></p>
            </li>
          </>
        )
      }

      function Upload() {
        return (
          <>
            <ol id="upload-nav-taps" alt="Upload nav taps">
              {UploadingFile()}
              {UploadingFile(true)}
              {UploadingFile()}
            </ol>
            <form id="upload-new-post">
              <div id="upload-top">
                <label>Post type: </label>
                <select id="submit-options" name="post-type" class="upload-input" required>
                  <option value="" disabled selected>Select a post type</option>
                  <option value="image">Image</option>
                  <option value="blog">Blog</option>
                </select>
              </div>
              <br />

              <div id="upload-bottom">
                <p class="big-text">Drop a image here, or click to upload.</p>
                <input id="file-input-button" type="file" name="image" accept="image/*" class="upload-input" required />
              </div>
              <br />

              <div id="upload-post-icons">
                <div class="vertical-hr"></div>

                <button id="cancel">Cancel</button>
                <input type="submit" value="Submit" class="submit" />

                <div class="vertical-hr"></div>
              </div>

            </form>
            <br />
          </>
        )
      }

      function Login() {
        return (
          <>
            <div id="login-form-box">
              <form id="login-form" type="POST">
                <div id="login-box">
                  <h1 class="extra-big-text">Login</h1>
                  <br />
                  <div id="login-input-box">
                    <input type="email" name="email" placeholder="email" class="login-input" />
                    <br />
                    <input type="password" name="password" placeholder="password" class="login-input" />
                    <br />
                  </div>
                  <br />
                  <div id="login-submit-box">
                    <div class="vertical-hr"></div>

                    <input type="submit" class="submit" />

                    <div class="vertical-hr"></div>
                  </div>
                  <br />
                  <p>
                    Can't log in, or have you forgotten your password?
                    <br />
                    No problem. Just send a text message to one of the admins on our official Discord server!
                    <br />
                    <a href="https://discord.gg/KUehpdtvvQ">https://discord.gg/KUehpdtvvQ</a>
                  </p>
                </div>
              </form>
            </div>
          </>
        )
      }
    </script>

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>