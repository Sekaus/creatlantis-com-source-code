const FileType = {
    "image" : 0,
    "model" : 1
}

 class File {
    path = "";
    metadata = {
        "name" : "",
        "type" : -1,
        "description" : "",
        "creationDate" : ""
    };

    constructor(path, metadata) {
        this.path = path;
        this.metadata = metadata;
    }
}

function UserMetadata() {
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

function Image(file) {
    return /*html*/`
        <img src="${file.path}" title="${file.metadata.name}" class="post"/>
    `;
}

function Comment(userMetadata, body) {
    return /*html*/ `
        <div class="comment">
          ${userMetadata}
          <div class="comment-body">${body}</div>
        </div>
    `;
}

function CommentSection() {
    return /*html*/ `
        <div id="comment-section">
            <p id="comment-section-title" class="big-text">Comment section</p>

            <hr/>

            <form id="add-new-comment">
                ${UserMetadata()}
                <textarea id="comment-input" cols="100" name="comment" rows="5" placeholder="Add a new comment..."/>

                <br/>

                <input type="submit" value="Submit" class="submit" disabled/>
            </form>

            <div id="comment-container">
                <!-- Comments here !-->
            </div>
        </div>
        `;
}