<!-- TO-DO: don't let me be a single file !--> 
<!-- Comment Stack  Start !-->

<div id="comment-stack" class="post-block">
    <h2>Comments</h2>
    <hr/>
    
    <!-- add a new comment !-->
    
    <div id="add-new-comment" class="post-block"> 
        <textarea placeholder="Add new commwnt..." rows="4" cols="50"></textarea>
         <button class="submit" onclick="addComment('<?php echo $_SESSION["uuid"]; ?>', 'post', this)">Submit</button>
    </div>
    
    <!-- load comments here !-->
    
</div>

<!-- Comment Stack End !-->