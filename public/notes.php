<?php
    session_start();
    
    include_once './php_functions/mysql_functions/load_content.php';
    include_once './php_functions/mysql_functions/login_handler.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Cache-Control" content="no-store, no-cache">
        <meta http-equiv="Pragma" content="no-cache">
        <title>Notes</title>
        <?php include_once './header.php' ?>
    </head>
    <body>
        <?php include_once './nav_bar.php' ?>
        
        <nav id="nav-taps">
            <ul>
                <li class="<?php echo ((!isset($_GET['tap']) || isset($_GET['tap']) && $_GET['tap'] == "to-you") ? "selectet-nav-tap" : "" ); ?>"><a href="notes.php?tap=to-you">Notes to you</a></li>
                <li class="<?php echo (isset($_GET['tap']) && $_GET['tap'] == "by-you" ? "selectet-nav-tap" : "" ); ?>"><a href="notes.php?tap=by-you">Notes you send</a></li>
            </ul>
        </nav>
        <div class="light-to-dark-shaded"><br/></div>
            <!-- Note inbox start !-->
            <div id="loaded-content">
            <div>
                <ul id="note-inbox" class="post-block">
                    <!-- note inbox display !-->
                </ul>
            </div>

            <div id="note-text" class="post-block">
                <div class="transmitter">
                    <!-- transmitter display !-->
                </div>
                <hr/>
                <p class="note-text"></p>
                <hr/>
                <div id="note-replies">
                    <!-- note reply's display !-->
                </div>
                <div id="note-options">
                    
                </div>
            </div>
        </div>
         
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-scrollTo/2.1.3/jquery.scrollTo.min.js"></script>
        
        <!-- Note inbox end !-->
        
        <script>
            // load note data from the MySQL database as JSON and load it as inbox
            
            <?php
                // check if the user is the owner of the profile before showing any notes
                if(verifyLogin($_SESSION['uuid'], $_SESSION['password'])) {
                    loadNotes($_SESSION['uuid'], $maxKeys + 6, $offset, (isset($_GET['tap']) && $_GET['tap'] == "by-you"));
                }
            ?>
                
            // load note from url
                  var index = '<?php echo ( (isset($_GET['new_index']) && (isset($_GET['old_index']) &&  $_GET['old_index'] != $_GET['new_index'])) ? $_GET['new_index']  : ""); ?>';
                    if(index !== "")
                        loadNoteText(index);
            
            // load note from index in loadedNotes and mark them as readed
            function loadNoteText(index) {
                // load note at index
                $('.transmitter .user-id').remove();
                setupUserInfoBox('#note-text .transmitter', loadedNotes[index].transmitter.uuid, 0);
                loadUserInfo(
                        "#note-text .transmitter", 
                        loadedNotes[index].transmitter.username, 
                        loadedNotes[index].transmitter.tagline, 
                        loadedNotes[index].transmitter.bio, 
                        loadedNotes[index].transmitter.date_of_birth, 
                        loadedNotes[index].transmitter.gender, 
                        loadedNotes[index].transmitter.profile_image
                );
                $('#note-text .note-text').html(loadedNotes[index].text);
                $("#note-options").html(
                       "<button class='edit' onclick='markNoteAs(false," + index + ");'>Mark as not readed</button>"
                    + "<button id='start-editing-note' class='edit' onclick='startEditingNote(" + index + ");'>Edit note</button>"
                    + "<button id='delete-note' class='action' onclick='deleteNotePopup(" + index + ");'>Delete</button>"
                    + "<button id='start-replying' class='submit' onclick='startReplyingNote(" + index + ")'>Reply</button>"
                  );
                  
                  // load note data
                  var json = [];
                  
                  <?php
                    if(isset($_GET['note_uuid']))
                        $profiles = loadAllNoteReplies($_SESSION['uuid'], $_GET['note_uuid']); 
                  ?>
                  
                  $(document).ready(function() {
                  <?php
                    if(isset($_GET['note_uuid'])) {
                        echo "loadNoteReplies(json);";
                        
                        $index = 0;
                        foreach ($profiles as $value) {
                            SetupAndLoadUserID('.note-replie[index="' . $index . '"] .transmitter', $value, 'false', $index);
                            $index++;
                        }
                    }
                  ?>;});
                  
                  if(json !== []) {
                  for(var i = 0; i < json?.length; i++) {
                        $("#note-replies").append(
                            '<div class="note-replie" index="' + i +'">' +
                                '<div class="transmitter">' +
                                  '<!-- transmitter display !-->' +
                                '</div>' +
                                '<hr/>' +
                                '<p class="note-text"></p>' +
                                '</div>' +
                                '<div id="note-options">' +
                                    '<!-- Display note options !-->' +
                                '</div>' +
                            '</div>' +
                            '<hr/>');
                        }
                  }
            }
            
            // scroll to loaded notes
            <?php if(isset($_GET['load_times'])) {?>
                $(document).ready(function () {
                    var offset = <?php echo isset($_GET['page_y_offset']) ? $_GET['page_y_offset'] : 0; ?>;
                    $('#note-inbox').scrollTop(offset);
                });
                
                $(document).ready(function () {
                     window.scrollTo(0, $(document).height());
                });
            <?php }?>
            
            // scroll to load more notes
            <?php if(!$atNoteStackEnd) {?>
            $("#note-inbox").scroll(function() {
                  var yOffset = $("#note-inbox").innerHeight() /<?php echo $loadedNoteCountSum;?> * 2;
                  // FIX-ME
                  if($( '#note-inbox' ).scrollTop() + $( '#note-inbox' ).innerHeight()  >= $( '#note-inbox' )[0].scrollHeight)
                        window.location.href = location.protocol + '//' + location.host + location.pathname + "?tap=<?php echo (isset($_GET['tap']) ? $_GET['tap'] : "to-you");?>" + "&load_times=" +<?php echo isset($_GET['load_times']) ? $_GET['load_times']+1 : 2;?> + "<?php echo (isset($_GET['tap']) && $_GET['tap'] == "show-comments") ? "&tap=show-comments" : ""?>" + "&page_y_offset=" + yOffset;
            });
            <?php }?>
            
             // mark read status on a anote
            function markNoteAs(readed, atIndex) {
                $("[index='" + atIndex + "'] .read-status").text((readed ? " " : "*"));
                $.ajax({
                    type: "POST",
                    url: "php_functions/mysql_functions/note_actions.php",
                    data: {
                        command: "mark_as_readed",
                        uuid: loadedNotes[atIndex].note_uuid,
                        recipient: "<?php echo $_SESSION['uuid']; ?>",
                        readed: readed ? "1" : "0"
                    },
                    success: function() {
                        reloadNotePage(atIndex, readed);
                    }, 
                    error: function (jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }
                });
            }
            
            // start editing a note
            function startEditingNote(noteIndex) {
                $('#start-editing-note').attr("hidden", true);
                $('#note-text').html("<textarea id='title' type='text'>" + loadedNotes[noteIndex].title + "</textarea><br/><textarea id='text' cols='15' rows='1' type='text'>" + loadedNotes[noteIndex].text + "</textarea><button id='save-note' class='submit' onclick='editNote(" +noteIndex  + ")'>Save</button><button class='edit' onclick='location.reload()'>Cancel</button>");
            }
            
            //  edit a note
            function editNote(atIndex) {
                $.ajax({
                    type: "POST",
                    url: "php_functions/mysql_functions/note_actions.php",
                    data: {
                        command: "edit",
                        uuid: loadedNotes[atIndex].note_uuid,
                        transmitter: "<?php echo $_SESSION['uuid'];?>",
                        title: $('#note-text #title').val(),
                        text: $('#note-text #text').val()
                    },
                    success: function() {
                        reloadNotePage(atIndex);
                    }, 
                    error: function (jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }
                });
            }
            
            // ask before deleting a note
            function deleteNotePopup(atIndex) {
                if (confirm("Are you sure you want to delete this note?")) {
                    deleteNote(atIndex);
                 }
            }
            
            // delete a note
            function deleteNote(atIndex) {
                $.ajax({
                    type: "POST",
                    url: "php_functions/mysql_functions/note_actions.php",
                    data: {
                        command: "delete",
                        uuid: loadedNotes[atIndex].note_uuid,
                        transmitter: "<?php echo $_SESSION['uuid']; ?>",
                    },
                    success: function() {
                        reloadNotePage(atIndex);
                    }, 
                    error: function (jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }
                });
            }
            
            // start replying a note
            function startReplyingNote(noteIndex) {
                $('#start-replying').attr("hidden", true);
                $('#note-replies').append("<textarea id='text' cols='15' rows='1' type='text'></textarea><button id='save-note' class='submit' onclick='replyNote(" +noteIndex  + ")'>Save</button><button class='edit' onclick='location.reload()'>Cancel</button>");
            }
            
            // reply to a note
            function replyNote(atIndex) {
                $.ajax({
                    type: "POST",
                    url: "php_functions/mysql_functions/note_actions.php",
                    data: {
                        command: "reply",
                        uuid: loadedNotes[atIndex].note_uuid,
                        transmitter: "<?php echo $_SESSION['uuid']; ?>",
                        text: $('#note-text #text').val()
                    },
                    success: function() {
                        reloadNotePage(atIndex);
                    }, 
                    error: function (jqXHR, textStatus, errorThrown){
                        console.log(errorThrown);
                    }
                });
            }
            
            // Load all note replies
            function loadNoteReplies(json) {
                for(i = 0; i < json?.length; i++) {
                    var selectedData = json[i];
                    
                   $('.note-replie[index="' + i + '"] .note-text').html(selectedData.text);
                }
            }
            
            // reload note page
            function reloadNotePage(atIndex, negetivIndex = false) {
                var tap= "<?php echo ((isset($_GET['tap']) && $_GET['tap'] == "to-you") || !isset($_GET['tap'])) ? "to-you" : "by-you";?>";
                window.location.href = location.protocol + '//' + location.host + location.pathname + "?tap=" + tap + "&new_index=" + atIndex + "&old_index=" + ( negetivIndex ? "-1" : "<?php echo (isset($_GET['new_index']) ? $_GET['new_index'] : "-1");?>") + "&note_uuid=" + loadedNotes[atIndex].note_uuid;
            }
        </script>
    </<body>
</html>