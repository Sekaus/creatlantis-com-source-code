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

function RulesAndPrivacyPopup() {
    return /*html*/ `
            <div id="rules-and-privacy-popup">
              <div id="rules-and-privacy-popup-content">
                <form action="" method="POST">
                  <h1 class="extra-big-text">Before you use the site!</h1>
                  <p class="big-text">You must go through and agree with the rules of our site and have read and accept our Terms of Service and Privacy Policy.</p>

                  <br/>

                  <div>
                    <strong>I have read and agree to the Terms of Service and rules for using this website. </strong>
                    <input name="had-read-terms-of-service" type="checkbox" value="yes" required />
                    <br />
                    Read the Terms of Service and the rules here: <a href="./html_documents/terms_of_use.html">Terms of Service</a>
                  </div>

                  <br/>

                  <div>
                    <strong>I have read and agree to the Privacy Policy for using this website. </strong>
                    <input name="had-read-privacy-policy" type="checkbox" value="yes" required />
                    <br />
                    Read the Privacy Policy here: <a href="./html_documents/private_policy.html">Privacy Policy</a>
                  </div>

                  <br/>

                  <div id="rules-and-privacy-popup-submit-box">
                    <div class="vertical-hr"></div>

                    <input name="read-and-agree" class="submit" type="submit" />

                    <div class="vertical-hr"></div>
                  </div>
                </form>
              </div>
            </div>
          `;
}