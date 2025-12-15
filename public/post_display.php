<?php
  session_start();

  include_once("./config.php");
  include_once("./data_handler.php");
  include_once("./data_filter.php");

  $dh_read = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);
  $viewedPost = null;

  if(isset($_GET['key'])) {
    $key = $dh_read->getKeyFromShortUUID($_GET['key']);
    if($key)
        $viewedPost = $dh_read->loadSingleFile($key);
  }

  $commentStack = $dh_read->loadCommentStack($_GET['key'], null);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Post name</title>
        <?php include_once("./html_elements/head.html"); ?>
    </head>
    <body>
        <?php include_once("./setup.php"); ?>
        <?php include_once("./navigation_bar.php"); ?>
        
        <div id="post-display">
            <!-- Display the post here -->

            <div id="post-icon-container" hidden>
                <div class="vertical-hr"></div>

                <button id="delete">Delete</button>

                <button id="edit">Edit</button>

                <div class="vertical-hr"></div>
            </div>

            <p id="post-display-title" class="extra-big-text">Title</p>

            <div id="post-feedback">
                <div class="vertical-hr"></div>
                <div>
                    <div id="post-feedback-top">
                        <div id="star-rate-box">
                            <var title="The number of reviews on this post" id="star-rate-reviews-count">-100</var>
                            <div id="star-rateing" title="Rate this post from 1 to 5 stars" style={{ "--star-rating" : "50%" }}
                                class="feedback-icon">
                                <div id="rate-1-star" class="rate-star-element"></div>
                                <div id="rate-2-star" class="rate-star-element"></div>
                                <div id="rate-3-star" class="rate-star-element"></div>
                                <div id="rate-4-star" class="rate-star-element"></div>
                                <div id="rate-5-star" class="rate-star-element"></div>
                            </div>
                            <var title="The average of the star rates on this post" id="star-rate-average">-5</var>
                        </div>

                        <var id="fave-count" title="The count of users adding this post to there favorites">-100</var>
                        <img id="add-to-favorites" title="Add the post to your favorites" src="./images/icons/faveIcon.webp" class="feedback-icon"/>

                        <var id="view-count" title="The count of unique views">-100</var>
                        <img src="./images/icons/viewIcon.webp" class="feedback-icon"/>

                        <var id="comment-count" title="The count of comments on this post">-100</var>
                        <img src="./images/icons/commentIcon.webp" class="feedback-icon"/>
                    </div>

                    <div id="post-feedback-bottom">
                        <!-- Post reactions here -->
                    </div>
                </div>

                <div class="vertical-hr"></div>
            </div>
            
            <hr/>

            <div id="description">Description...</div>
        </div>
        
        <?php include_once("./html_elements/footer.html"); ?>

        <script type="module">
            import {Image, Journal, PostType, CommentSection, Comment, LoadCommentStack} from "./js/common.js";

            /* Load post data */
            <?php if($viewedPost != null): ?>
                let postData = JSON.parse(<?php echo json_encode($viewedPost); ?>);
                 
                $(document).prop('title', postData.metadata.title);
                $("#post-display-title").html(postData.metadata.title);

                let $postContainer = $("#post-display");

                switch(postData.type) {
                    case PostType.IMAGE:
                        $postContainer.prepend(Image(postData.src));
                        $('#description').html(postData.metadata.description);
                        break;
                    case PostType.JOURNAL:
                        $("#post-display-title").remove();
                        $('#description').remove();
                        $postContainer.prepend(Journal(postData.body));
                        break;
                }

                $postContainer.append(CommentSection());
                
                // Load comments (no repies)
                <?php if($commentStack != null): ?>
                    $(document).ready(function() {
                        LoadCommentStack(`<?php echo $commentStack; ?>`);
                    });
                <?php endif; ?>
            <?php else: ?>
                $("#post-display").html(/*html*/ `
                    <div id="post-feedback">
                        <div>
                            <h1 class="extra-big-text">ERROR 404</h1>
                            <br/>
                            <p>Post could not be found... :/</p>
                        </div>
                    </div>
                `);
            <?php endif; ?>

            function PostReaction(emoji) {
                return /*html*/ `
                    <div class="post-reaction">
                        <var title="The count of this reaction" class="reaction-count">-5</var>
                        <p title="Add this reaction to this post" class="reaction-emoji">${emoji}</p>
                    </div>
                `;
            }
        </script>
    </body>
</html>