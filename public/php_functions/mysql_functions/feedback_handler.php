<?php
    include_once 'store_data.php';
    
    /* Perform Action Based on the Post Feedback Type Value */
    if(isset($_POST['post_link']) && isset($_POST['post_feedback_type']) && isset($_POST['post_feedback_value'])) {
        // update a old feedback or linke to a new one
        storeFeedBack($_POST['post_link'], $_POST['post_feedback_type'], $_POST['post_feedback_value']);
    }
    else
        echo "ERROR: Invalid feedback input...";
?>