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
                  <li class="inbox-content">Content</li>
                  <li class="inbox-content">Content</li>
                  <li class="inbox-content">Content</li>
                </ol>
              </div>

              <div id="inbox-content-box">
                <p id="inbox-name">Note name ▼</p>

                <div id="selected-inbox-content">
                  <div id="note-content">
                    <div id="note-top">
                      <!-- User metadata here -->
                      <p id="note-title">Select a note to view its content.</p>
                      <div class="note-text">
                        <!-- Note answer here -->
                      </div>
                    </div>

                    <br/>
                    <hr/>
                    <br/>

                    <div id="note-bottom">
                      <!-- User metadata here -->
                      <div class="note-text">
                          <!-- Original note here --> 
                      </div>
                    </div>
                  </div>

                  <div id="watching-content">
                    <!-- Watching content here -->
                    <p>Select a watching item to view its content.</p>
                  </div>
                </div>
            </div>
        </div>

        <?php include_once("./html_elements/footer.html"); ?>
        
        <script>
          const inboxFilter = {
            "notes": 0,
            "watching": 1
          };

          let selectedInboxFilter = inboxFilter.notes;

          $(document).ready(function() {
            $("#watching-content").hide();

            $(".inbox-filter").click(function() {
              $(".inbox-filter").removeClass("selected-inbox-filter");
              $(this).addClass("selected-inbox-filter");

              if ($(this).text() === "Notes") {
                selectedInboxFilter = inboxFilter.notes;
                $("#note-content").show();
                $("#watching-content").hide();
              } else {
                selectedInboxFilter = inboxFilter.watching;
                $("#note-content").hide();
                $("#watching-content").show();
              }
            });

            $(".inbox-content").click(function() {
              $(".inbox-content").removeClass("selected-inbox");
              $(this).addClass("selected-inbox");
            });
          });
        </script>
    </body>
</html>