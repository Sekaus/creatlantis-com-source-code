export const PostType = {
    IMAGE: "image",
    JOURNAL: "journal"
}

export function UserMetadata(main = false) {
    return /*html*/`
        <a id="${main ? 'main-user' : ""}" href="#" title="Go to the user's profile" class="profile-link">
          <img class="user-icon" src="../images/default_pp.webp"/>
          <div class="user-metadata">
            <p class="user-name big-text">Name</p>
            <p class="user-tagline">Tagline</p>
          </div>
        </a>
    `;
}

export function Image(path, title = "", key = "") {
    return /*html*/`
        <img src="${path}" ` + (title != "" ? `title="${title}"` : ``) + ` class="post" data-key="${key}"/>
    `;
}

export function Journal(content, key = "") {
    return /*html*/`
        <div class="journal-content post" data-key="${key}">
            ${content}
        </div>
    `;
}

export function DisplayLoadedPost(posts, traget) {
    posts.forEach(element => {
        switch(element.type) {
        case "image":
            $(traget).append(Image(element.src, element.title, element.key));
            break;
        case "journal":
            $(traget).append(Journal(element.body, element.key));
            break;
        }
    });
}

export function OnPostThumbClick(post) {
    var key = $(post).attr("data-key");
    window.location.href = `post/${key}`;
}

export function Comment(stackUUID, body) {
    return /*html*/ `
        <div class="comment" data-stack-uuid="${stackUUID}">
          ${UserMetadata()}
          <div class="comment-body">${body}</div>
          <div class="loaded-replies">
            <button class="load-replies" title="Load more replies from this comment">Replies</button>
            <!-- Load in replies here -->
        </div>
        </div>
    `;
}

export function LoadCommentStack(commentStack) {
    const comments = JSON.parse(commentStack);

    comments.forEach(comment => {
        const $commentBody = $(Comment(comment.stack_uuid, comment.comment));

        const $loadBtn = $commentBody.find(".load-replies");
        const $replyContainer = $commentBody.find(".loaded-replies");

        $loadBtn.one("click", function () {
            LoadReplies(comment.stack_uuid);
            $(this).remove();
        });

        $("#comment-container").append($commentBody);
    });
}

function LoadReplies(stackUUID) {
    const $container = $("[data-stack-uuid='" + stackUUID + "']");

    // Guard: already loaded
    if ($container.data("replies-loaded")) return;
    $container.data("replies-loaded", true);

    $.ajax({
        url: "comment_handler.php",
        type: "POST",
        dataType: "json",
        data: {
            stack_uuid: stackUUID,
            stack_command: "load_replies"
        },
        success: function (json) {
            if (!json || !json.success || !json.array.length) return;

            json.array.forEach(comment => {
                const $commentBody = $(Comment(comment.stack_uuid, comment.comment));

                const $loadBtn = $commentBody.find(".load-replies");
                const $replyContainer = $commentBody.find(".loaded-replies");

                $loadBtn.one("click", function () {
                    LoadReplies(comment.stack_uuid);
                    $(this).remove();
                });

                $container.find(".loaded-replies").append($commentBody);
            });
        },
        error: function (xhr) {
            console.error(xhr.responseText);
        }
    });
}

export function CommentSection() {
    return /*html*/ `
        <div id="comment-section">
            <p id="comment-section-title" class="big-text">Comment section</p>

            <hr/>

            <form id="add-new-comment">
                ${UserMetadata(true)}
                <textarea id="comment-input" cols="100" name="comment" rows="5">Add a new comment...</textarea>

                <br/>

                <input type="submit" value="Submit" class="submit" />
            </form>

            <div id="comment-container">
                <!-- Comments here !-->
            </div>
        </div>
    `;
}

export function Folder() {
    return /*html*/ `
        <li class="folder">
            <img src={defaultImg} class="folder-thumbnail" />
            
            <p class="folder-name">Name</p>
              <div class="folder-icon-container">
                <img src={folderContentIcon} class="folder-icon" />
                <var title="The number of items in this folder" class="folder-content-count">-999</var>
              </div>
        </li>
    `;
}