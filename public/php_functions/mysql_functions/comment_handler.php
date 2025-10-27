<?php
    include_once 'store_data.php';
    include_once '../data_filter.php';
    
    /* Perform Action Based on the Add Comments POST Data */
    if(isset($_POST['from_uuid']) && (isset($_POST['to']) && $_POST['to'] != 'NaN' ) && isset($_POST['to_type']) && (isset($_POST['comment']) && strlen($_POST['comment']) > 0) && isset($_POST['reply_uuid'])) {
        // add comment to comment stack
        storeComment($_POST['from_uuid'], ($_POST['to_type'] == 'post' ? $_POST['to'] : NULL), ($_POST['to_type'] != 'post' ? $_POST['to'] : NULL), filterUnwantedCode($_POST['comment']), $_POST['reply_uuid']);
    }
    else
        echo "ERROR: Invalid comment input...";
?>