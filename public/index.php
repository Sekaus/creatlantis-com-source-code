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

    <?php include_once("./html_elements/footer.html"); ?>
  </body>
</html>