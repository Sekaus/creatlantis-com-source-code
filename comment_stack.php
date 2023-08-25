 <!-- Comment Stack  Start !--> 
 
 <script>
     // FIX-ME: replys don't link to comment
     let newReply = '<!-- add a new comment !-->' +
            '<h3>Add new reply:</h3>' +
            '<div id="add-new-comment" class="post-block">' +
                '<textarea placeholder="Add new commwnt..." rows="4" cols="50"></textarea>' +
                '<button class="submit" onclick="addComment(' + "'<?php echo $_SESSION["uuid"]; ?>'" + ', this, this, ' + ')">Submit</button>' +
            '</div>';
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

<!-- Comment Stack End !-->