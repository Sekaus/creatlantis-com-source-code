<?php
    session_start();
    
    include_once 'php_functions/mysql_functions/store_data.php';
    include_once 'php_functions/mysql_functions/load_content.php';
?>

<!doctype html>
<html>
    <head>
        <?php include_once './header.php'?>
    </head>
    <body>
        <?php include_once './nav_bar.php';?>
        
        <!--Setup submit folder page !-->
        <div id="loaded-content">
            <form id='form-post' class="post-block" action="submit_folder.php<?php echo isset($_GET['edit_folder_uuid']) ? "?edit_folder_uuid=" . $_GET['edit_folder_uuid'] : '';?>" enctype="multipart/form-data" method="POST">
                <fieldset>
                    <input name="title" maxlength="99" type="text" placeholder="Title..." required/>
                    
                    <input name="thumbnail" type="text" placeholder="Thumbnail URL...">
                    
                    <textarea name="description" cols="30" rows="2" placeholder="Description..."></textarea>
                    
                <button class="submit" value="Submit" onclick="upload()">Submit</button>
            
                </fieldset>
            </form>
        </div>
        
        <?php include_once './progressbar.php'; ?>
        
        <?php include_once './footer.php';?>
        
        <script>
            // hide search filters
            $('#search-filters').hide();
            
            // disabled submit button clicking and start the upload
            var isUploading = false;
            function upload() {
                // checking each required input for empty value
                var valid = true;
                $('[required]').each(function() {
                    if($.trim($(this).val()) && valid != false)
                        valid = true;
                    else
                        valid = false;
                });

                if(valid && !isUploading) {
                    isUploading = true;
                    
                    // show and start the loading screen
                    startLoadingScreen();
                }
            };
            
            <?php
                if(isset($_GET['edit_folder_uuid']))  {  
                    // get folder from database
                    $stmt = $mysqli->prepare(
                           "SELECT * FROM folder_stack "
                         . "WHERE folder_uuid=? "
                         . "LIMIT 1 "
                     );

                     $stmt->bind_param("s", $_GET['edit_folder_uuid']);
                     $stmt->execute();

                     $result = $stmt->get_result();

                     $row = $result->fetch_assoc();

                     loadFolder($row, false, true);
                }
            
                if(isset($_POST['title'])) {
                    if(!isset($_GET['edit_folder_uuid'])) {
                        addFolder($_SESSION['uuid'], $_POST['title'], $_POST['description'], ($_POST['thumbnail'] == "" ? "./images/default_sp.webp" : $_POST['thumbnail']));
                        echo 'window.location.href = "profile.php?profile_id=' . $_SESSION['uuid'] . '&tap=show-gallery";';
                    }
                    else {
                        editFolder($_SESSION['uuid'], $_POST['title'], $_POST['description'], ($_POST['thumbnail'] == "" ? "./images/default_sp.webp" : $_POST['thumbnail']), $_GET['edit_folder_uuid']);
                        echo 'window.location.href = "profile.php?profile_id=' . $_SESSION['uuid'] . '&tap=show-gallery&folder=' . $_GET['edit_folder_uuid'] . '";';
                    }
                }
            ?>
        </script>
    </body>
</html>