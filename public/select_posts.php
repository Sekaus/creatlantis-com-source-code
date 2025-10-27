<?php
    session_start();
    
    include_once "./php_functions/mysql_functions/store_data.php";
    include_once './php_functions/mysql_functions/load_content.php';
    
    // store last selected posts in a session
    if(isset($_POST['selected_posts_array']))
        $_SESSION['selected_posts_array'] = $_POST['selected_posts_array'];
    
    // delete last selected posts in a session
    if(isset($_POST['clear_selected_posts_array']))
        unset($_SESSION['selected_posts_array']);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Select Posts</title>
        <?php include_once './header.php';?>
    </head>
    <body>
        <script>
            let  selectedPostsArray = <?php echo isset($_SESSION['selected_posts_array']) ? $_SESSION['selected_posts_array'] : "[]"?>;
        </script>
        
        <?php include_once './nav_bar.php';?>
        
        <h1>Select <?php echo (isset($_GET['element_index']) ? "a post": "some posts");?> from profile.</h1>
        <div id="post-options" class="post-block"><button class="submit" onclick="submitSelectedPosts('<?php echo (isset($_GET['element_index']) ? "submit_to_profile_element" : "submit_to_folder" );?>')">I'm done</button></div>
        
        <!-- loaded posts start !-->
        
        <?php include_once './loaded_posts_nav.php'; ?>
        
        <!-- loaded posts end !-->
        
        <?php include_once './footer.php';?>
        
        <script>
            <?php
                if(isset($_GET['mode'])) {
                    if($_GET['mode'] == "add_item")
                        loadContentFromUser($maxKeys, $offset, $_SESSION['uuid'], $_GET['folder']);
                    else
                        loadContentFromFolder($maxKeys, $offset, $_GET['folder']);
                }
            ?>
                
            // disable post links and replace them with post-link attributes
            $('#loaded-content').children().each(function() {
                $(this).attr("post-link", $(this).attr('href'));
                $(this).removeAttr('href');
            });
            
            // event handler for any click on the post-block elements
            var maxSelectCount = "<?php echo (isset($_GET['element_index']) ? 'one' : "infinite");?>";
            $("#loaded-content .post-block").on("click", function(){
                if(maxSelectCount === "one") {
                    selectedPostsArray = [];
                    $("#loaded-content .post-block").removeClass("selected-post-block");
                }
               
                <?php if(!isset($_GET['folder'])) {?>
                    selectedPostsArray.push($($(this).prop('outerHTML')).attr("href", $(this).attr('post-link')).removeAttr('post-link').addClass("full-size").prop('outerHTML'));
                <?php } else {?>
                    var selectedPost = $(this).attr('post-link').replace('post_display.php?post_link=', '');
                    if(!selectedPostsArray.includes(selectedPost))
                        selectedPostsArray.push(selectedPost);
                    else
                        selectedPostsArray.splice(selectedPostsArray.indexOf(selectedPost));
                    
                    // store the selected posts in POST data
                    $.ajax({
                            url: './select_posts.php?folder=<?php echo isset($_GET['folder']) ? $_GET['folder'] : ""; ?>&mode=<?php echo isset($_GET['mode']) ? $_GET['mode'] : ""; ?>',
                            method: 'POST',
                            data: {
                                selected_posts_array: JSON.stringify(selectedPostsArray)
                            },
                            success: (data) => {
                              console.log(data);
                            },
                            error: (xhr, textStatus, error) => {
                              console.error('Request failed. Status code: ' + xhr.status);
                            }
                        });
                <?php };?>
                    
                $(this).toggleClass("selected-post-block");
            });
            $(document).ready(function(){
                selectedPostsArray.forEach(function(item){
                    $('[post-link*="' + item + '"]').addClass("selected-post-block");
                });
            });
            
            // submit selectedPostsArray data
            function submitSelectedPosts(command) {
                // show and start the loading screen
                startLoadingScreen();
                
                // save to AWS S3
                if(command === "submit_to_profile_element") {
                    // get profile design from AWS S3
                    let profileElementArray = <?php echo json_encode(json_decode(getS3Object($_SESSION['uuid'] . "/json/profile_design.json", false)['Body'])->data);?>;
                    
                    // modify profile design
                    profileElementArray.slotArray[<?php echo isset($_GET['element_index']) ? $_GET['element_index'] : 0;?>].CustomHTML = selectedPostsArray[0];
                    
                    $.ajax({
                        url: 'php_functions/s3_functions/uploader_post_actions.php',
                        method: 'POST',
                        data: {
                            data_type:  'profile_design',
                            data: JSON.stringify(profileElementArray)
                        },
                        success: (data) => {
                          console.log(data);
                          window.location.href = "profile.php?profile_id=<?php echo $_SESSION['uuid'];?>";
                        },
                        error: (xhr, textStatus, error) => {
                          console.error('Request failed. Status code: ' + xhr.status);
                        }
                    });
                }
                // save to MySQL database
                else if(command === "submit_to_folder" && <?php echo isset($_GET['folder']) ? "true" : "false";?>) {
                    $.ajax({
                        url: 'php_functions/mysql_functions/folder_actions.php?folder=<?php if(isset($_GET['folder'])) echo $_GET['folder'];?>&mode=<?php echo $_GET['mode'];?>',
                        method: 'POST',
                        data: {
                            data: JSON.stringify(selectedPostsArray)
                        },
                        success: (data) => {
                          console.log(data);
                          window.location.href = "profile.php?profile_id=<?php echo $_SESSION['uuid'];?>&tap=show-gallery&folder=<?php echo $_GET['folder'] ? $_GET['folder'] : 'all';?>";
                        },
                        error: (xhr, textStatus, error) => {
                          console.error('Request failed. Status code: ' + xhr.status);
                        }
                    });
                    
                    resetSelectedPosts()
                }
            }
            
            function resetSelectedPosts() {
                $.ajax({
                    url: './select_posts.php',
                    method: 'POST',
                    data: {
                        clear_selected_posts_array: true
                     },
                     success: (data) => {
                        console.log(data);
                     },
                     error: (xhr, textStatus, error) => {
                        console.error('Request failed. Status code: ' + xhr.status);
                     }
                  });
            }
        </script>
    </body>
</html>
