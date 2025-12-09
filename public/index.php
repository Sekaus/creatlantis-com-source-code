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
    <?php include_once("./html_elements/navigation_bar.html"); ?>
    <?php include_once("./setup.php"); ?>

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
    
    <?php include_once("./html_elements/footer.html"); ?>
    <script type="module">
      import {Image, Journal} from "./js/common.js";

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
      
      posts.forEach(element => {
        switch(element.type) {
          case "image":
            $("#content-view").append(Image(element.src, element.title));
            break;
          case "journal":
            $("#content-view").append(Journal(element.body));
            break;
        }
      });
    </script>
  </body>
</html>