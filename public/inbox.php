<?php
  session_start();

  include_once("./data_handler.php");
  include_once("./config.php");

  $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);

  $inboxNotes = $dh->loadUserInboxNotes(unserialize($_SESSION["user_login"]), unserialize($_SESSION["user_data"]));
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
                  <!-- Inbox content here -->
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
        let inboxContent = <?php echo $inboxNotes; ?>;

        // note.uuid = this note's own ID
        // note.reply = parent note UUID, or null/undefined if this is a top-level note

        const noteMap = new Map();
        const repliesByParent = new Map();

        inboxContent.notes.forEach(note => {
          noteMap.set(note.uuid, note);

          if (note.reply) {
            if (!repliesByParent.has(note.reply)) {
              repliesByParent.set(note.reply, []);
            }
            repliesByParent.get(note.reply).push(note);
          }
        });

        // Only show top-level notes in the sidebar
        const topLevelNotes = inboxContent.notes.filter(note => !note.reply);

        // 3. Load the Inbox Sidebar
        topLevelNotes.forEach((note, index) => {
          $("#inbox-content-list").append(`
            <li class="inbox-content" data-note-uuid="${note.uuid}">
              <date>${note.date}</date>
              <br/>
              ${note.title && note.title.trim() !== "" ? note.title : "No Title"}
            </li>
          `);
        });

        function renderReplies(parentUuid, container, depth = 0) {
          const replies = repliesByParent.get(parentUuid) || [];

          replies.forEach(reply => {
            const margin = depth * 20;
            container.append(`
              <div class="reply-item" style="margin-left:${margin}px; border-left:1px solid #ccc; padding-left:10px; margin-top:10px;">
                <div><strong>${reply.title && reply.title.trim() !== "" ? reply.title : "No Title"}</strong></div>
                <div class="note-text">${reply.text}</div>
              </div>
            `);

            renderReplies(reply.uuid, container, depth + 1);
          });
        }

        $(document).ready(function() {
          $("#watching-content").hide();

          // Filter Toggle
          $(".inbox-filter").click(function() {
            $(".inbox-filter").removeClass("selected-inbox-filter");
            $(this).addClass("selected-inbox-filter");
            const isNotes = $(this).text() === "Notes";
            $("#note-content").toggle(isNotes);
            $("#watching-content").toggle(!isNotes);
          });

          // Note Selection Logic
          $(document).on("click", ".inbox-content", function () {
            $(".inbox-content").removeClass("selected-inbox");
            $(this).addClass("selected-inbox");

            const uuid = $(this).data("note-uuid");
            const note = noteMap.get(uuid);

            $("#inbox-name").text(note.title && note.title.trim() !== "" ? note.title : "View Note");
            $("#note-title").text(note.title && note.title.trim() !== "" ? note.title : "Message Content");
            $("#note-top .note-text").text(note.text);

            $("#note-bottom .note-text").empty();
            $("#note-bottom").show();
            $("hr").show();

            const repliesContainer = $("#note-bottom .note-text");
            renderReplies(note.uuid, repliesContainer);
          });
        });
      </script>
    </body>
</html>