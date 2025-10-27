<?php
    session_start();
    
    include_once '../../php_functions/data_filter.php';
    include_once 'store_data.php';
    include_once 'load_content.php';
    
   /* Perform Action Based on the Note Info Value  */
    
   if(isset($_POST['title']) && $_POST['title'] != "" && isset($_POST['text']) && $_POST['text'] != "" && isset($_POST['profile_id']) && $_POST['profile_id'] != "") {
         sendNode($_SESSION['uuid'] , $_POST['profile_id'], filterUnwantedCode($_POST['title']), filterUnwantedCode($_POST['text']));

         header("Location: ../../profile.php?profile_id=" . $_POST['profile_id']);
   }
   
   if(isset($_POST['command']) &&  isset($_POST['uuid']) && $_POST['uuid'] != "") {
        if($_POST['command'] == "mark_as_readed" && isset($_POST['recipient']) && $_POST['recipient'] != "") {
            if( isset($_POST['readed']) && $_POST['readed'] != "") {
              setNoteReadStatus($_POST['uuid'], $_POST['recipient'], $_POST['readed']);
         }
       }
       else if($_POST['command'] == "edit"  && isset($_POST['transmitter']) && $_POST['transmitter'] != "") { 
           if(isset($_POST['title']) && $_POST['title'] != "" && isset($_POST['text']) && $_POST['text'] != "")
              editNote($_POST['transmitter'], $_POST['uuid'], $_POST['title'], $_POST['text']);
       }
       else if($_POST['command'] == "delete"  && isset($_POST['transmitter']) && $_POST['transmitter'] != "") { 
           deleteNote($_POST['transmitter'], $_POST['uuid']);
       }
       else if($_POST['command'] == "reply"  && isset($_POST['transmitter']) && $_POST['transmitter'] != "") { 
           if(isset($_POST['text']) && $_POST['text'] != "")
              replyNote($_POST['transmitter'], $_POST['uuid'], $_POST['text']);
       }
   }
   
   header("Location: ../../notes.php");
?>