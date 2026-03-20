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
    <?php include_once("./navigation_bar.php"); ?>
    <?php include_once("./setup.php"); ?>

    <div id="index-box">
      <ol id="category-list">
        <li className="category-filter selected-category">All</li>
        <li className="category-filter">Drawings</li>
        <li className="category-filter">Paintings</li>
        <li className="category-filter">Other</li>
      </ol>

      <div id="content-view">
        <!-- Post links here -->
      </div>
    </div>
    
    <?php include_once("./html_elements/footer.html"); ?>
    <script type="module">
      import {Image, Journal, DisplayLoadedPost, OnPostThumbClick} from "./js/common.js";

      // Page data
      let posts = "";
      <?php
        global $dbConfig;
        global $s3Config;

        include_once("./data_handler.php");

        // Now create DataHandle correctly:
        $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

        echo "posts = " . $dh->loadAllFiles(FileType::all, "", FileLoadOrder::newest, 10, 0) . ";";
      ?>

      DisplayLoadedPost(posts, "#content-view");

      $(document).ready(function() {
        const category = {
          "all": 0,
          "drawings": 1,
          "paintings": 2,
          "other": 3
        }

        let selectedCategory = category.all;

        $(".post").click(function() {
          $()

          OnPostThumbClick(this);
        });
      });
    </script>
  </body>
</html>