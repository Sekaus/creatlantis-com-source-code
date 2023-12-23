<?php
    session_start();
    
    $absolute_path = dirname(__FILE__);
    include_once "db_connect.php";
    include_once "$absolute_path/../../php_functions/s3_functions/object_loader.php";
    include_once "$absolute_path/../../php_functions/data_filter.php";
    
    /* Filter, Order and Search Engine */
    
    $filter = "%";
    $order = "DESC";
    $search = "%";
        
    // get filter variable input data
    if(isset($_GET['filter']) && $_GET['filter'] != "all")
        $filter = $_GET['filter'];
        
    // get order variable input data
    if(isset($_GET['order']) && $_GET['order'] == "oldest")
        $order = "ASC";
       
    // get search variable input data
    if(isset($_GET['search']))
        $search = "%" . base64_decode(urldecode($_GET['search'])) . "%";
    
    static $offset = 0;
    static $maxKeys = 10;
    static $postCount = 0;
    static $commentOffset = 0;
    static $commentCount = 0;
    
    // update post count and offset GUI
    function updatePostCountAndOffset($message = 'no more posts have been made yet...') { 
        global $offset;
        global $postCount;
        
        // check if post count is over 0
        if($postCount > 0) {
            // if it is over 0, update post count and offset in the loaded post nav
            echo "$('#post-count-and-offset').html('" . ($offset + 1) . " - " . ($postCount + $offset) . "');"; 
        }
        else {
            // if not, print to the user that there are no posts made yet
            echo "$('#post-count-and-offset').html('$message');";
        }
    }
    
    // update comment count and offset GUI
    /*function updateCommentCountAndOffset() { 
        global $commentOffset;
        global $commentCount;
        
        // check if post count is over 0
        if($commentCount > 0) {
            // if it is over 0, update post count and offset in the loaded post nav
            echo "$('#comment-count-and-offset').html('" . ($commentOffset + 1) . " - " . ($commentCount + $commentOffset) . "');"; 
        }
        else {
            // if not, print to the user that there are no posts made yet
            echo "$('#comment-count-and-offset').html('no more comments have been made yet...');";
        }
    }*/
    
    /* Load in on Page Functions */
    
    // load all content from post_list from offset to offset + maxKeys    
    function loadContentFromAll($maxKeys, $offset) {
        global $mysqli;
        global $postCount;
        global $filter;
        global $order;
        global $search;

        $stmt = $mysqli->prepare(
                    "SELECT * FROM post_list "
                  . "WHERE type LIKE ? "
                  . "AND (tags LIKE ? OR title LIKE ?) "
                  . "ORDER BY date $order "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        
        $stmt->bind_param("sssii", $filter, $search, $search, $maxKeys, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // count rows and update post count and offset
        $postCount = mysqli_num_rows($result);
        updatePostCountAndOffset();
        
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            if($row['id'] >= 0) {
                // output data of each row
                $key = strchr($row['link'] , $row['owner']);
                echo loadS3Object($key);
            }
        }
        $result->close();
    }

    // load all content from post_list from offset to offset + maxKeys from a single user   
    function loadContentFromUser($maxKeys, $offset, $uuid) {
        global $mysqli;
        global $postCount;
        global $filter;
        global $order;
        global $search;
        
        $stmt = $mysqli->prepare(
                    "SELECT * FROM post_list "
                  . "WHERE owner=? "
                  . "AND type LIKE ? "
                  . "AND (tags LIKE ? OR title LIKE ?) "
                  . "ORDER BY date $order "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        $stmt->bind_param("ssssii", $uuid, $filter, $search, $search, $maxKeys, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // count rows and update post count and offset
        $postCount = mysqli_num_rows($result);
        updatePostCountAndOffset();
        
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            // output data of each row
            $key = strchr($row['link'], $uuid);
            echo loadS3Object($key);
        }
        $stmt->close();
    }
    
    // load all profiles from user_info from offset to offset + maxKeys
    function loadAllProfiles($maxKeys, $offset) {
        global $mysqli;
        global $postCount;
        global $order;
        global $search;
        
        $stmt = $mysqli->prepare(
                    "SELECT uuid FROM user_info "
                  . "WHERE username LIKE ? "
                  . "ORDER BY registration_date $order "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        $stmt->bind_param("sii", $search, $maxKeys, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // count rows and update profile count and offset
        $postCount = mysqli_num_rows($result);
        updatePostCountAndOffset('no more profiles to find...');
        
        // output data of each row
        $nextIndex = 0;
        while ($row = $result->fetch_assoc()) {
            // output data of each row
            SetupAndLoadUserID('#loaded-content', $row['uuid'], 'false', $nextIndex++);
        }
        $result->close();
    }

    // display a post in full size
    function displayPostInFullSize($postKey) {
        // load and display post in full size
        echo loadS3Object($postKey, true);
        
        /* Load and Display Post Metadata */
        
        // display the tags to the post
        loadTagsFromPost($postKey);
        
        // display the date of the post
        loadDateFromPost($postKey);
        
        // get the owner's uuid
        $ownerUUID = getS3Object($postKey, false)['Metadata']['owner'];
        
        // display the owner of the post
        SetupAndLoadUserID('.second-user', $ownerUUID);
    }

    // load submit date from post key
    function loadDateFromPost($postKey) {
        global $mysqli;
        $linkPart = "%$postKey";
        
        $stmt = $mysqli->prepare("SELECT date FROM post_list WHERE link LIKE ?");
        $stmt->bind_param("s", $linkPart);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            echo loadDate($row['date']);
        }
    }


    // load all tags from post key
    function loadTagsFromPost($postKey, $returnAsString = false) {
        global $mysqli;
        $tagsArray;
        $linkPart = "%$postKey";
        
        $stmt = $mysqli->prepare("SELECT tags FROM post_list WHERE link LIKE ?");
        $stmt->bind_param("s", $linkPart);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // check if the tags data should be return as a string or be echo as links
            if(!$returnAsString) {
                // if the data of tags in the selected row should be printed as links
                // make a array of tags of it's data
                $tagsArray = preg_split("/\#/", $row['tags']);
                $tagsArray = preg_replace("/ /i", '', $tagsArray);
            }
            else {
                // if the data of tags in selected row should be return as a string
                // just return it's data
                return $row['tags'];
            }
        }
        $stmt->close();
        
        // check if the tags array is set to a count (is a array that contain any tags at all)
        if(isset($tagsArray[0])) {
            // if the tags array is set to a count
            // print each tag in tags array as a link
            foreach ($tagsArray as $tag) {
                echo loadTag("$tag");
            }
        }
    }
    
    // load in feedback form post at $post_link
    function loadFeedback($post_link) {
        global $mysqli;
        
        // get post id
        $stmt_get = $mysqli->prepare("SELECT id FROM post_list WHERE link LIKE ?");
        $post_link = "%$post_link%";
        $stmt_get->bind_param("s", $post_link);
        $stmt_get->execute();
        
        $result = $stmt_get->get_result();
        $postID = $result->fetch_row()[0] ?? false;
        
        // check if post at post id exsist
        if($postID == false) {
            $stmt_get->close();
            exit("ERROR: invalid post_link: $post_link");
        }
        
        $stmt_get->close();
        
        /* Get Star Rate Results on post */
        
        // get star rate
        $stmt_star_rate = $mysqli->prepare("SELECT COUNT(star_rate), SUM(star_rate) AS total_star_rate FROM feedback WHERE post_id=? AND star_rate > 0");
        $stmt_star_rate->bind_param("i", $postID);
        $stmt_star_rate->execute();
        
        $result = $stmt_star_rate->get_result();
        $row = $result->fetch_row();
        $starRateVoteCount = $row[0] ?? 0;
        $starRateSum = $row[1] ?? 0;
        
        // get the average of the star rate 
        if($starRateVoteCount != 0 && $starRateSum != 0)
            $starRateAverage = $starRateSum/$starRateVoteCount;
        else
            $starRateAverage = 0;
        
        $stmt_star_rate->close();
        
        // wrap the loaded star rate results in a JSON string
        $loadedStarRate = "{'rateAverage': $starRateAverage, 'voteCount': $starRateVoteCount}";
        
        /* Get Fave Results on Post */
        $stmt_faves = $mysqli->prepare("SELECT COUNT(fave), uuid, fave FROM feedback WHERE post_id=? AND fave=1");
        $stmt_faves->bind_param("i", $postID);
        $stmt_faves->execute();
        
        $result = $stmt_faves->get_result();
        $row = $result->fetch_row();
        $loadedFaveCount = $row[0] ?? 0;
        
        // check if user has fave the post or not
        $hasMainUserClickedOnFave = (($row[1] == $_SESSION['uuid'] && $row[2] == 1) ? true : false);
        
        // wrap the loaded fave results in a JSON string
        $loadedFave = "{'faveCount': $loadedFaveCount, 'userHasFave': " . json_encode($hasMainUserClickedOnFave) . "}";
        
        $stmt_faves->close();
        
        /* Get View Count on Post */
        $stmt_view_count = $mysqli->prepare("SELECT COUNT(post_id) FROM feedback WHERE post_id=?");
        $stmt_view_count->bind_param("i", $postID);
        $stmt_view_count->execute();
        
        $result = $stmt_view_count->get_result();
        $loadedViewCount = $result->fetch_row()[0] ?? 0;
        
        $stmt_view_count->close();
        
        /* Return the feedback data */

        // return a JSON string with the feedback data
        echo "loadFeedback({ 'starRate': $loadedStarRate, 'faves': $loadedFave, 'viewCount': $loadedViewCount });";
        
        // count comments on post
        CountComments($postID);
    }
    
    // load in faves form a user
    function loadFavesFromUser($maxKeys, $offset, $uuid) {
        global $mysqli;
        global $postCount;
        global $filter;
        global $order;
        global $search;
        
        // select all faved posts (post_id) by $uuid 
        $stmt_id = $mysqli->prepare(
                    "SELECT post_id FROM feedback "
                  . "WHERE uuid=? AND fave=1 "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        
        $stmt_id->bind_param("sii", $uuid, $maxKeys, $offset);
        $stmt_id->execute();
        
        $result_id = $stmt_id->get_result();
        
        // count rows and update post count and offset
        $postCount = mysqli_num_rows($result_id);
        updatePostCountAndOffset();
        
        // output data of each row
        while ($row_id = $result_id->fetch_assoc()) {
            // select all posts from post_list by id
            $stmt_posts = $mysqli->prepare(
                        "SELECT * FROM post_list "
                      . "WHERE id=? AND type LIKE ? "
                      . "AND (tags LIKE ? OR title LIKE ?) "
                      . "ORDER BY date $order "
                      . "LIMIT ? "
                      . "OFFSET ?"
                    );

            $stmt_posts->bind_param("isssii", $row_id['post_id'], $filter, $search, $search, $maxKeys, $offset);
            $stmt_posts->execute();

            $result_posts = $stmt_posts->get_result();
            
            while ($row_post = $result_posts->fetch_assoc()) {
                $key = strchr($row_post['link'] , $row_post['owner']);
                echo loadS3Object($key);
            }
            $stmt_posts->close();
        }
        $stmt_id->close();
    }
    
    // load in a single comment
    function loadComment($row, $loadedUsers) {
        global $postObject;
        
        // get and store the offset of a loadad user
            $userIndexOffset = -1;
            if(!in_array($row['uuid'], $loadedUsers)) {
                array_push($loadedUsers, $row['uuid']);
                $userIndexOffset = (count($loadedUsers) - 1);
            }
            else if(in_array($row['uuid'], $loadedUsers))
                $userIndexOffset = array_search($row['uuid'], $loadedUsers);
            
            if($userIndexOffset != -1) {
                // get data from comment
                $uuid = $row['uuid'] ;
                $comment = $row['comment'];
                $date = $row['date'];
                $stackUUID = $row['stack_uuid'];
                $replyUUID = (($row['reply_uuid'] == NULL) ? '' : $row['reply_uuid']);
                
                // print loaded comment
                echo "loadComment('" . $comment . "', '" . $date . "', '" . $stackUUID . "', '" . $replyUUID .  "', '" . ((($_SESSION['uuid'] == $uuid) || (isset($_GET['profile_id']) && ($_SESSION['uuid'] == $_GET['profile_id'])) || (isset($postObject) && $postObject['Metadata']['owner'] == $_SESSION['uuid'])) ? 1 : 0)  .  "', '" . ($_SESSION['uuid'] == $uuid ? 1 : 0)  . "');";
                    
                // load a user's info
                SetupAndLoadUserID("#comment-stack .comment[data-id=$stackUUID]", $uuid, 'false', $userIndexOffset);
           }
    }
    
    // load in comments
    // TO-DO: make me more simple
    // NOTE:  loadComments works best after comment_stack.php is loaded
    // TO-DO: clean me up
    function loadComments($postLink = -1, $profile_uuid = NULL, $maxKeys, $offset, $looping) {
        global $mysqli;
        //global $commentCount;
        $loadTimes = $looping ? $_GET['load_times'] : 1;
        for($times = 0; $times < $loadTimes; $times++) {
            // get post id form post_list or -1
            $postID = -1;
            if($postLink != -1) {
                $stmt_post_id = $mysqli->prepare("SELECT id FROM post_list WHERE link LIKE ?");
                $postLink = "%$postLink%";
                $stmt_post_id->bind_param("s", $postLink);
                $stmt_post_id->execute();

                $result = $stmt_post_id->get_result();
                $postID = $result->fetch_row()[0] ?? -1;

                $stmt_post_id->close();
            }

            // get comment from database
            // TO-DO: order by date DESC somehow
            $stmt = $mysqli->prepare(
                        "SELECT * FROM comment_stack "
                       . "WHERE ((post_id=? AND (post_id IS NOT NULL AND post_id!= -1)) OR (profile_uuid=? AND profile_uuid IS NOT NULL)) "
                       . "ORDER BY date ASC "
                       . "LIMIT ? "
                       . "OFFSET ?"
                    );

            $stmt->bind_param("isii", $postID, $profile_uuid, $maxKeys, $offset);
            $stmt->execute();

            $result = $stmt->get_result();

            // count rows and update post count and offset
            //$commentCount = mysqli_num_rows($result);
            //updateCommentCountAndOffset();
        
            $loadedUsers = [];
            while ($row = $result->fetch_assoc()) {
                loadComment($row, $loadedUsers);
            }
            
            $offset += $maxKeys;
        }
    }
    
    // count comments on post
    function CountComments($postID = 0, $profileUUID = "") {
        global $mysqli;
        global $commentCount;

        // count comments on post
        $stmt = $mysqli->prepare(
                 "SELECT COUNT(stack_uuid) "
                . "FROM comment_stack "
                . "WHERE post_id=? OR profile_uuid=?"
            );
        
        $stmt->bind_param("is", $postID, $profileUUID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $count = $row[0];
        
        // print out the count value
        echo "readCommentCount($count);";
        
        $commentCount = $count;
    }
    
    // load all watchers profiles from watchers_stack from offset to offset + maxKeys
    function loadWatchersProfiles($maxKeys, $offset, $uuid) {
        global $mysqli;
        
        $stmt = $mysqli->prepare(
                    "SELECT uuid FROM watchers_stack "
                  . "WHERE watcher_uuid=? "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        $stmt->bind_param("sii", $uuid, $maxKeys, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // output data of each row
        $nextIndex = 0;
        while ($row = $result->fetch_assoc()) {
            // output data of each row
            SetupAndLoadUserID('#loaded-watchers', $row['uuid'], 'false', $nextIndex++);
        }
        $result->close();
    }
    
    // load all watching profiles from watchers_stack from offset to offset + maxKeys
    function loadWatchingProfiles($maxKeys, $offset, $uuid) {
        global $mysqli;
        
        $stmt = $mysqli->prepare(
                    "SELECT watcher_uuid FROM watchers_stack "
                  . "WHERE uuid=? "
                  . "LIMIT ? "
                  . "OFFSET ?"
                );
        $stmt->bind_param("sii", $uuid, $maxKeys, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        // output data of each row
        $nextIndex = 0;
        while ($row = $result->fetch_assoc()) {
            // output data of each row
            SetupAndLoadUserID('#loaded-watching', $row['watcher_uuid'], 'false', $nextIndex++);
        }
        $result->close();
    }
    
    // count watchers on profile
    function CountWatchers($profileID) {
        global $mysqli;
        
        // count comments on post
        $stmt = $mysqli->prepare(
                 "SELECT COUNT(uuid) "
                . "FROM watchers_stack "
                . "WHERE watcher_uuid=?"
            );
        
        $stmt->bind_param("s", $profileID);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $count = $row[0];
        
        // print out the count value
        echo "readWatchCount($count);";
    }

    // load a user id and set up its HTML
    function SetupAndLoadUserID($HTMLTaget, $uuid, $mainUserSeeingProfile = 'false', $userIndexOffset = 0) {
        // setup main user id HTML
        echo "setupUserInfoBox('$HTMLTaget', '$uuid', $userIndexOffset);";
        
        // load a user's info
        loadUserInfo($HTMLTaget, $uuid, $mainUserSeeingProfile, $userIndexOffset);
    }
    
    // load and print user info as HTML elements (PS. keep in mind that non of the sensitive user data is printed)
    function loadUserInfo($HTMLTaget, $uuid, $mainUserSeeingProfile = 'false', $userIndexOffSet = 0) {
        global $mysqli;
        
        $stmt = $mysqli->prepare("SELECT * FROM user_info WHERE uuid=?");
        $stmt->bind_param("s", $uuid);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // load in user info
            echo "loadUserInfo('" .  
                    $HTMLTaget . "', '" 
                    . htmlentities($row['username']) . "', '" 
                    . htmlentities($row['tagline']) . "', '" 
                    . htmlentities($row['bio']) . "', '"
                    . $row['date_of_birth'] . "', '" 
                    . htmlentities($row['gender']) . "', '" 
                    . $row['profile_image'] . "', '" 
                    . $mainUserSeeingProfile . "', '" 
                    . $userIndexOffSet . "');";
        }
    }
    
    // load and print user login info as HTML elements (PS. keep in mind that password NEVER gonna be printed)
    function loadUserLogin() {
        global $mysqli;
        
        $stmt = $mysqli->prepare("SELECT * FROM user_info WHERE uuid=? AND password=PASSWORD(?)");
        $stmt->bind_param("ss", $_SESSION['uuid'], $_SESSION['password']);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            echo "loadUserLogin(' " . $row['email'] . " ');";
        }
    }
    
    // check if main user have read and accept the Terms of Service and Privacy Policy
    function readAndAcceptPopup() {
        global $mysqli;
        
        $stmt = $mysqli->prepare("SELECT read_and_accept_opup FROM user_info WHERE uuid=?");
        $stmt->bind_param("s", $_SESSION['uuid']);
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // return enum value
            return $row['read_and_accept_opup'];
        }
    }
?>