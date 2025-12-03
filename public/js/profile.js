export function StartEditingProfile() {
    $("#custom-profile-view").hide();
    $(".custom-profile-editor").show();
    $("#start-editing-profile").hide();
}

export function EndEditingProfile(button, isAUser, profileJSON) {
    if ($(button).hasClass("submit")) {
        if(isAUser) {
            $.ajax({
                url: "./custom_profile_handle.php?isAUser=false",
                method: "POST",
                data: {
                    profile_design: profileJSON
                },
                contentType: false,
                processData: false,

                success: function(response) {
                    alert("Profile update Complete!");
                    location.reload();
                },
                error: function(xhr) {
                    alert("Profile update failed: " + JSON.parse(xhr.responseText).error);
                }
            });
        }
    }
    else
        location.reload();
}

export function CustomProfileView() {
    return /*html*/ `
        <div id="custom-profile-view">
            <div id="custom-profile-left">
                <!-- Custom profile elements left side here -->
            </div>
            <div id="custom-profile-right">
                <!-- Custom profile elements right side here -->
            </div>
        </div>
    `;
}

export function CustomProfileEdit() {
    return /*html*/`
        <div id="custom-profile-edit" class="custom-profile-editor">
            <!-- In use profile elements here -->
        </div>

        <div id="profile-layout-icon-container" class="custom-profile-editor">
            <div class="vertical-hr"></div>

            <button id="cancel">Cancel</button>
            <button class="submit">Submit</button>

            <div class="vertical-hr"></div>
        </div>

        <div id="profile-element-box" class="custom-profile-editor">
            <!-- Unusd profile elements here -->
        </div>
    `;
}

/* Profile elements */

export function CustomProfileElement(content = /*html*/`<div class="custom-content">test</div>`) {
    return /*html*/ `
        <div class="custom-profile-element profile-element">
            <div class="profile-element-icon-container">
                <img src="./images/icons/editIcon.webp" id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon"/>
            </div>
            ${content}
        </div>
    `;
}

export function CommentSectionElement() {
    return /*html*/ `
        <div id="comment-section-element" class="profile-element">
            ${CommentSection()}
        </div>
    `;
}

export function PostSpotlightElement(imageFile, title) {
    return /*html*/ `
        <div class="post-spotlight-element profile-element">
            <div class="profile-element-icon-container">
                <img src="./images/icons/editIcon.webp" id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon" />
            </div>
            <p class="post-spotlight-title big-text">${title}</p>
            <div class="post-spotlight">
                ${imageFile}
            </div>
        </div>
    `;
}

export function ProfileBIOElement() {
    return /*html*/ `
        <div id="profile-bio-element" class="profile-element">
            <div id="profile-bio">
                <h1 class="big-text">About me</h1>

                <div class="bio-content-box">
                    <img class="user-icon" src="./images/default_pp.webp" />
                </div>

                <div class="bio-content-box">
                    <div>
                        <p class="extra-big-text">Name</p>
                    </div>
                </div>

                <hr/>

                <div class="bio-content-box">
                    <label class="big-text">Hobbies</label>
                    <p id="hobbies" class="bio-content">Gameing / Singing / Talking</p>
                </div>

                <hr/>

                <div class="bio-content-box">
                    <label class="big-text">BIO</label>
                    <div id="user-bio" class="bio-content">
                        <!-- BIO content here -->
                    </div>
                </div>

                <hr/>
            </div>
        </div>
    `;
}