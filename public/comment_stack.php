<!-- Comment Stack  Start !--> 
 
 <script>
     // scroll to loaded comments
     <?php if(isset($_GET['load_times'])) {?>
        $(document).ready(function () {
            window.scrollTo(0, <?php echo isset($_GET['page_y_offset']) ? $_GET['page_y_offset'] - 38 : 0;?>);
         });
     <?php }?>
     
     // scroll to load more comments
     <?php if(!isset($_GET['tap']) || $_GET['tap'] == 'show-comments' || $_GET['tap'] == 'show-profile' || isset($_GET['post_link'])) {?>
        let commentOffset = 10;
        $(window).scroll(function() {
             if($(window).scrollTop() == $(document).height() - $(window).height()) {
                 window.location.href = location.protocol + '//' + location.host + location.pathname + "<?php echo isset($_GET['post_link']) ? "?post_link=" . $_GET['post_link'] : "?profile_id=" . $_GET['profile_id']?>" + "&load_times=" +<?php echo isset($_GET['load_times']) ? $_GET['load_times']+1 : 2;?> + "<?php echo (isset($_GET['tap']) && $_GET['tap'] == "show-comments") ? "&tap=show-comments" : ""?>" + "&page_y_offset=" + window.pageYOffset;
            }
        });
     <?php }?>
     
     let newReply = '<!-- add a new comment !-->' +
            '<h3>Add new reply:</h3>' +
            '<div id="add-new-comment" class="post-block">' +
                '<textarea placeholder="Add new commwnt..." rows="4" cols="50"></textarea>' +
                '<button class="submit" onclick="addComment(' + "'<?php echo $_SESSION["uuid"]; ?>'" + ', this, this);">Submit</button>' +
            '</div>';
    
    let editAComment = '<!-- add a new comment !-->' +
            '<h3>Edit comment:</h3>' +
            '<div id="edit-comment" class="post-block">' +
                '<textarea placeholder="edit commwnt..." rows="4" cols="50"></textarea>' +
                '<button class="submit" onclick="editComment(this);">Submit</button>' +
            '</div>';
    
    // TO-DO: make me less hacky
    function autoFillEditText(target) {
        $('#edit-comment textarea').text($(target).parent().parent('.comment').find('.comment-text').first().text());
    }
            
            // send a comment command to do an action on a user comment
            function userCommentAction(command, commentUUID, commentText='') {
                switch(command) {
                    case 'edit_comment':
                        sendCommentRequestAsAJAX(command, commentUUID, commentText);
                        break;
                    case 'delete_comment':
                        // FIX-ME: Not deleting any comment
                        deleteComment(commentUUID);
                        break;
                }
            }
                
            // ask user to confirm before deleteing the post
            function deleteComment(commentUUID) {
                if (confirm('Are you sure you want to delet this comment (can not be undo)')) {
                    //delete the post
                    sendCommentRequestAsAJAX("delete_comment", commentUUID);
                }
            }
            
            // edit a comment
            function editComment(element) {
                userCommentAction('edit_comment', $(element).parent().parent().parent().attr('data-id'), $(element).closest('.post-block').find('textarea').val());
            }
            
            function sendCommentRequestAsAJAX(command, comment, commentText = "") {
                xhr.onreadystatechange = function() {
                    // when getting a response back
                    if (this.readyState === 4 && this.status === 200) {
                        //log response
                        console.log(this.responseText);
                        
                        location.reload();
                    }
                };
                
                // send post command by GET reqerst
                var url = 'php_functions/mysql_functions/user_comment_actions.php?comment_command='+command+"&stack_uuid="+comment+"&comment_text="+commentText;
                xhr.open('GET', url, true);
                xhr.send();
            };
            
            var xhr = new XMLHttpRequest();
            
            $(document).ready(function () {
                $('#comment-stack').append('<h3>Scroll to load more comments and replies.</h3>');
            });
 </script>
 
 <div id="comment-stack" class="post-block">
    <h2>Comments</h2>
    <hr/>
    
    <!-- add a new comment !-->
    <h3>Add new comment:</h3>
    <div id="add-new-comment" class="post-block">
        <textarea placeholder="Add new commwnt..." rows="4" cols="50"></textarea>
        <button class="submit" onclick="addComment('<?php echo $_SESSION["uuid"]; ?>', this)">Submit</button>
    </div>
    
    <!-- load comments here !-->
</div>

<?php //include_once './loaded_comments_nav.php'; ?>
 
<!-- Comment Stack End !-->