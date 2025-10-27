
<!-- Write note popup start !-->

<div id="write-note-box" hidden>
    <h1>Write a note:</h1>
    <form id="write-note"  action="./php_functions/mysql_functions/note_actions.php" enctype="multipart/form-data" method="POST">
        <fieldset>
            <label>Title:</label>
            <input name="title"  placeholder="Title..." required/>
            <label>Text:</label>
            <textarea name="text" cols="30" rows="2" placeholder="Write a note..." required></textarea>
            <button type="submit" class="submit" value="Submit">Send</button><a class='edit' href="" onclick="$('write-note-box').hide()">Cancel</a>
            <textarea name="profile_id" hidden><?php echo isset($_GET['profile_id']) ? $_GET['profile_id'] : "";?></textarea>
        </fieldset>
    </form>
</div>    

<!-- Write note popup end !-->