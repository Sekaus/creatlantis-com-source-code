<!DOCTYPE html>
<html>
    <head>
        <title>Post name</title>
        <?php include_once("./html_elements/head.html"); ?>
    </head>
    <body>
        <?php include_once("./setup.php"); ?>
        <?php include_once("./html_elements/navigation_bar.html"); ?>
        
        <div id="post-display">
            {Image(testImg0)}

            <div id="post-icon-container" hidden>
                <div class="vertical-hr"></div>

                <button id="delete">Delete</button>

                <button id="edit">Edit</button>

                <div class="vertical-hr"></div>
            </div>

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
                        <img id="add-to-favorites" title="Add the post to your favorites" src={faveIcon}
                            class="feedback-icon" />

                        <var id="view-count" title="The count of unique views">-100</var>
                        <img src={viewIcon} class="feedback-icon" />

                        <var id="comment-count" title="The count of comments on this post">-100</var>
                        <img src={commentIcon} class="feedback-icon" />
                    </div>

                    <div id="post-feedback-bottom">
                        {PostReaction('🤣')}
                        {PostReaction('💯')}
                        {PostReaction('🤘')}
                    </div>
                </div>

                <div class="vertical-hr"></div>
            </div>

            {CommentSection()}
        </div>
        
        <?php include_once("./html_elements/footer.html"); ?>

        <script>
            $(document).prop('title', 'test')

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