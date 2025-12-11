export const PostType = {
    IMAGE: "image",
    JOURNAL: "journal"
}

export function UserMetadata() {
    return /*html*/`
        <a href="#" title="Go to the user's profile" class="profile-link">
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

export function Comment(userMetadata, body) {
    return /*html*/ `
        <div class="comment">
          ${userMetadata}
          <div class="comment-body">${body}</div>
        </div>
    `;
}

export function CommentSection() {
    return /*html*/ `
        <div id="comment-section">
            <p id="comment-section-title" class="big-text">Comment section</p>

            <hr/>

            <form id="add-new-comment">
                ${UserMetadata()}
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