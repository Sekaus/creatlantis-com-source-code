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
    
    <?php include_once("./html_elements/side_render.html"); ?>
    <?php include_once("./html_elements/footer.html"); ?>
    <script>
      // Page data
      <?php
        include_once("./data_handler.php");

        $dbConfig = [
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'Test-13579',
            'database' => 'userdb',
            'port'     => 3306
        ];

        $s3Config = [
            'bucket_or_arn'   => 'creatlantis-com-s3-private',
            'region'          => 'eu-north-1',
            'use_path_style'  => false,
            'use_arn_region'  => false               // not needed unless using ARNs
        ];

        // Now create DataHandle correctly:
        $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

        echo "let posts = '" . $dh->loadAllFiles(FileType::all, "", FileLoadOrder::newest, 10, 0) . "';";
      ?>


    </script>
  </body>
</html>