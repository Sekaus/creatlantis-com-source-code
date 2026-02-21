<?php
  session_start();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Inbox</title>
        <?php include_once("./html_elements/head.html"); ?>
    </head>
    <body>
        <?php include_once("./navigation_bar.php"); ?>
        <?php include_once("./setup.php"); ?>

        <div id="inbox-box">
            <div id="inbox-map">
                <ol id="inbox-filter-list">
                  <li class="inbox-filter selected-inbox-filter">Notes</li>
                  <li class="inbox-filter">Watching</li>
                </ol>
                <br/>
                <ol id="inbox-content-list">
                  <li class="inbox-content">Content</li>
                  <li class="inbox-content">Content</li>
                  <li class="inbox-content selected-inbox">Content</li>
                  <li class="inbox-content">Content</li>
                  <li class="inbox-content">Content</li>
                </ol>
              </div>

              <div id="inbox-content-box">
                <p id="inbox-name">Note name ▼</p>

                <div id="selected-inbox-content">
                  <div id="note-top">
                    {UserMetadata()}
                    <div class="note-text">
                      <!-- Note answer here -->
                    </div>
                  </div>

                  <br/>
                  <hr/>
                  <br/>

                  <div id="note-bottom">
                    {UserMetadata()}
                    <div class="note-text">
                        <!-- Original note here --> 
                    </div>
                  </div>
                </div>
            </div>
        </div>

        <?php include_once("./html_elements/footer.html"); ?>
    </body>
</html>