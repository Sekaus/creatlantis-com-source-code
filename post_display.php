<?php
    include_once './php_functions/s3_functions/object_loader.php';
    include_once './php_functions/mysql_functions/load_content.php';
    
    // check for bad request
    if(!isset($_GET['post_link'])) {
        echo "Error 400: post at link is not found...";
    }
    else {  
        /* verify that the user is the owner of the post */
        
        $postObject = getS3Object($_GET['post_link'], false);
        $isMainUserNotTheOwner = ($postObject['Metadata']['owner'] != $_SESSION['uuid']);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Profile</title>
        <?php include_once './header.php'?>
    </head>
    <body>
        <?php include_once './nav_bar.php';?>
        
        <!-- profile info end !-->
        
        <div id="loaded-content">
            <!-- display post in full size !-->
        </div>
        
        <!-- add edit and delete buttons on the post in full size mode !-->
        <div id="post-options" class="post-block">
            <button class="edit modify-post" onclick="userPostAction('edit_post')">Edit</button>
            <button class="action modify-post" onclick="userPostAction('delete_post')">Delete</button>
        </div>
        
        <!-- metadata display !-->
        <div id="metadata" class="post-block">
            <div class="user-info-box second-user">
                <!-- owner display !-->
            </div>
            
            <h3 id="submit-date"></h3>
            
            <div id="tags">
                <!-- display post tags !-->
            </div>
        </div>
        
        <?php include_once './comment_stack.php';?>
        
        <?php include_once './footer.php';?>
        
        <!-- Load In Data for Content !--> 
        <script>
            // hide search filters
            hideSearchFilters = true;
            
            // if the post does not belong to the main user, hide the edit and delete buttons
            var hideEditAndDelete = <?php echo json_encode($isMainUserNotTheOwner); ?>;
            if(hideEditAndDelete)
                $('#post-options .modify-post').hide();
            
            /* load post content */
            <?php
                // display post in full size
                displayPostInFullSize($_GET['post_link']); 
                
                // load in feedback form post
                loadFeedback($_GET['post_link']);
                
                // load in comments form post
                static $commentStackOffset = 0;
                loadComments($_GET['post_link'], NULL, 10, $commentStackOffset);
                
                // disply the main user's id in the comment stack
                SetupAndLoadUserID('#comment-stack #add-new-comment', $_SESSION['uuid']);
            ?>
            // add one view to the post's feedback data if it is not the owner of the post that see it
            if(hideEditAndDelete)
                updateFeedback('view', '');
            
            //send a post command to do an action on a user post
            function userPostAction(command) {
                var mainPost = '<?php echo $_GET['post_link']; ?>';
                switch(command) {
                    case 'edit_post':
                        sendRequestAsAJAX(command, mainPost);
                        break;
                    case 'delete_post':
                        deletePost(mainPost);
                        break;
                }
            }
                
            //ask user to confirm before deleteing the post
            function deletePost(post) {
                if (confirm('Are you sure you want to delet this post (can not be undo)')) {
                    //delete the post
                    sendRequestAsAJAX("delete_post", post);
                }
            }
            
            function sendRequestAsAJAX(command, post) {
                xhr.onreadystatechange = function() {
                    //when getting a response back
                    if (this.readyState === 4 && this.status === 200) {
                        //log response
                        console.log(this.responseText);
                        
                        //leads user to another page
                        if(command === 'edit_post')
                            window.location.href = './submit.php?post=overwrite';
                        else if(command === 'delete_post')
                            window.location.href = './profile.php?profile_id=<?php echo $_SESSION['uuid']; ?>';
                    }
                };
                
                //send post command by POST reqerst
                var url = 'php_functions/s3_functions/user_post_actions.php?post_command='+command+"&post="+post;
                xhr.open('GET', url, true);
                xhr.send();
                //window.location.href = './profile.php';
            };
            
            var xhr = new XMLHttpRequest();
        </script>
    </body>
</html>