import { BBCodeRender } from "./text_formatter.js";
import { CommentSection } from "./common.js";

export function CustomProfileView() {
    return /*html*/ `
        <div id="custom-profile-view">
            <div id="custom-profile-left">
                <!-- Profile elements left side here -->
            </div>
            <div id="custom-profile-right">
                <!-- Profile elements right side here -->
            </div>
        </div>
    `;
}

export function CustomProfileEdit() {
    return /*html*/`
        <div id="custom-profile-edit" class="custom-profile-editor">
            <!-- In use profile elements here -->
            
            <div id="custom-profile-left-edit">
                <!-- Profile elements left side here -->
            </div>
            <div id="custom-profile-right-edit">
                <!-- Profile elements right side here -->
            </div>
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

const ElementType = {
    CUSTOM:             0,
    COMMENT_SECTION:    1,
    SPOTLIGHT:          2,
    BIO:                3,
    COLLECTION:         4
}

class ProfileElement {
    type = null;
    body = "";
    inEdit = "";

    // include inEdit in the serialized JSON so editing can access it
    JSON() {
        return JSON.stringify({
            body: this.body,
            inEdit: this.inEdit,
            type: this.type
        });
    }
}

export class CustomProfileElement extends ProfileElement {
    type = ElementType.CUSTOM;

    constructor(
        content = /*html*/`
            <div class="custom-content">
                <div style="display: flex; justify-content: center; background-color: white">
                    [url=https://glitter-graphics.com/myspace/text_generator.php]
                        [img]https://text.glitter-graphics.net/cbl/w.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/e.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/l.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/c.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/o.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/m.gif[/img]
                        [img]https://text.glitter-graphics.net/cbl/e.gif[/img]
                        [img]https://dl3.glitter-graphics.net/empty.gif[/img]
                    [/url]
                </div>
            </div>
        `) {
        super();

        this.body = /*html*/ `
            <div class="custom-profile-element profile-element">
                <div class="profile-element-icon-container">
                    <!-- changed to class to avoid ID collisions -->
                    <img src="./images/icons/editIcon.webp" class="start-editing-profile-icon profile-element-icon" title="Start editing this profile element" alt="Edit icon" />
                </div>
                ${content}
            </div>
        `;

        this.inEdit = /*html*/ `
            <div class="custom-profile-element profile-element">
                <p class="post-spotlight-title big-text">Custom Element</p>
                ${content}
            </div>
        `;
    }
}

export class CommentSectionElement extends ProfileElement {
    type = ElementType.COMMENT_SECTION;

    constructor() {
        super();

        this.body = /*html*/ `
            <div id="comment-section-element" class="profile-element">
                ${CommentSection()}
            </div>
        `;

        this.inEdit = this.body;
    }
}

export class PostSpotlightElement extends ProfileElement {
    type = ElementType.SPOTLIGHT;

    constructor(imageFile = "./images/default_img.webp", title = "Title") {
        super();

        this.body = /*html*/ `
            <div class="post-spotlight-element profile-element">
                <div class="profile-element-icon-container">
                    <!-- changed to class to avoid ID collisions -->
                    <img src="./images/icons/editIcon.webp" class="start-editing-profile-icon profile-element-icon" title="Start editing this profile element" alt="Edit icon" />
                </div>
                <p class="post-spotlight-title big-text">${title}</p>
                <div class="post-spotlight">
                    <img src="${imageFile}"/>
                </div>
            </div>
        `;

        this.inEdit = /*html*/ `
            <div class="post-spotlight-element profile-element">
                <p class="post-spotlight-title big-text">Spotlight</p>
                <div class="post-spotlight">
                    <img src="./images/default_img.webp"/>
                </div>
            </div>
        `;
    }
}

export class ProfileBIOElement extends ProfileElement {
    type = ElementType.BIO;

    constructor() {
        super();

        this.body = /*html*/ `
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

         this.inEdit = this.body;
    }
}

const CountOnElements = {
    [ElementType.CUSTOM]:          4,
    [ElementType.COMMENT_SECTION]: 1,
    [ElementType.SPOTLIGHT]:       4,
    [ElementType.BIO]:             1,
};

function RenderBBCode() {
    $(".profile-element").each(function() {
        $(this).html(BBCodeRender($(this).html()));
    });
}

export function LoadProfileElements(profileDesignJSON) {
    // view containers (unchanged)
    $("#custom-profile-left").empty();
    $("#custom-profile-right").empty();

    profileDesignJSON.elements.left.forEach(element => {
        $("#custom-profile-left").append(JSON.parse(element).body);
    });
    profileDesignJSON.elements.right.forEach(element => {
        $("#custom-profile-right").append(JSON.parse(element).body);
    });

    RenderBBCode();
}

export function StartEditingProfile(profileDesignJSON) {
    $("#custom-profile-view").hide();
    $(".custom-profile-editor").show();
    // hide only the page-level editor button
    $("#start-editing-profile").hide();

    // Clone the count object properly
    let countOnUnusedElements = { ...CountOnElements };

    // Subtract used elements (left & right) and append their inEdit variant
    $("#custom-profile-left-edit").empty();
    $("#custom-profile-right-edit").empty();

    profileDesignJSON.elements.left.forEach(e => {
        const parsed = JSON.parse(e);
        countOnUnusedElements[parsed.type]--;
        // parsed.inEdit is now present because JSON() serializes it
        $("#custom-profile-left-edit").append(parsed.inEdit);
    });
    profileDesignJSON.elements.right.forEach(e => {
        const parsed = JSON.parse(e);
        countOnUnusedElements[parsed.type]--;
        $("#custom-profile-right-edit").append(parsed.inEdit);
    });

    // Loop through element types and populate the unused pool
    $("#profile-element-box").empty();
    for (let type in countOnUnusedElements) {
        let count = countOnUnusedElements[type];
        type = Number(type);

        while (count > 0) {
            let newElement = null;

            switch (type) {
                case ElementType.CUSTOM:
                    newElement = new CustomProfileElement();
                    break;
                case ElementType.COMMENT_SECTION:
                    newElement = new CommentSectionElement();
                    break;
                case ElementType.SPOTLIGHT:
                    newElement = new PostSpotlightElement();
                    break;
                case ElementType.BIO:
                    newElement = new ProfileBIOElement();
                    break;
            }

            if (newElement) {
                $("#profile-element-box").append(newElement.inEdit);
            }

            count--;
        }

        RenderBBCode();
    }
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
