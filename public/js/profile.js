import { BBCodeRender } from "./text_formatter.js";
import { CommentSection } from "./common.js";

function getDropPositionElement(container, mouseY) {
    const children = [...container.querySelectorAll('.profile-element')];

    for (const child of children) {
        const rect = child.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;

        // If cursor is above the midpoint, drop before this child
        if (mouseY < midpoint) {
            return child;
        }
    }

    // If cursor is below all midpoints → insert at end
    return null;
}

/* ------------------------
   Views
   ------------------------ */
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
            <!-- Unused profile elements here -->
        </div>
    `;
}

/* ------------------------
   Element types & base class
   ------------------------ */
export const ElementType = {
    CUSTOM:             0,
    COMMENT_SECTION:    1,
    SPOTLIGHT:          2,
    BIO:                3,
    COLLECTION:         4
};

class ProfileElement {
    constructor(type = null, body = "", inEdit = "") {
        this.type = type;
        this.body = body;
        this.inEdit = inEdit;
    }

    // JSON serializable representation
    JSON() {
        return JSON.stringify({
            type: this.type,
            body: this.body,
            inEdit: this.inEdit
        });
    }
}

/* ------------------------
   Concrete elements
   Important: each element's HTML uses a single outer wrapper with class `profile-element`
   and an inner `.element-body` that is safe to rewrite by BBCodeRender. Avoid nested `.profile-element`.
   ------------------------ */
export class CustomProfileElement extends ProfileElement {
    constructor(content = /*html*/`
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
        super(ElementType.CUSTOM);

        this.body = /*html*/ `
            <div class="profile-element" data-type="${ElementType.CUSTOM}">
                <div class="element-body">
                    <div class="custom-profile-element">
                        <div class="profile-element-icon-container">
                            <img src="./images/icons/editIcon.webp" class="start-editing-profile-icon profile-element-icon" title="Start editing this profile element" alt="Edit icon" />
                        </div>
                        ${content}
                    </div>
                </div>
            </div>
        `;

        this.inEdit = /*html*/ `
            <div class="profile-element" data-type="${ElementType.CUSTOM}">
                <div class="element-body">
                    <div class="custom-profile-element">
                        <p class="post-spotlight-title big-text">Custom Element</p>
                        ${content}
                    </div>
                </div>
            </div>
        `;
    }
}

export class CommentSectionElement extends ProfileElement {
    constructor() {
        super(ElementType.COMMENT_SECTION);

        this.body = /*html*/ `
            <div class="profile-element" data-type="${ElementType.COMMENT_SECTION}">
                <div class="element-body">
                    <div id="comment-section-element" class="comment-section-element">
                        ${CommentSection()}
                    </div>
                </div>
            </div>
        `;

        this.inEdit = this.body;
    }
}

export class PostSpotlightElement extends ProfileElement {
    constructor(imageFile = "./images/default_img.webp", title = "Title") {
        super(ElementType.SPOTLIGHT);

        this.body = /*html*/ `
            <div class="profile-element" data-type="${ElementType.SPOTLIGHT}">
                <div class="element-body">
                    <div class="post-spotlight-element">
                        <div class="profile-element-icon-container">
                            <img src="./images/icons/editIcon.webp" class="start-editing-profile-icon profile-element-icon" title="Start editing this profile element" alt="Edit icon" />
                        </div>
                        <p class="post-spotlight-title big-text">${title}</p>
                        <div class="post-spotlight">
                            <img src="${imageFile}"/>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.inEdit = /*html*/ `
            <div class="profile-element" data-type="${ElementType.SPOTLIGHT}">
                <div class="element-body">
                    <div class="post-spotlight-element">
                        <p class="post-spotlight-title big-text">Spotlight</p>
                        <div class="post-spotlight">
                            <img src="./images/default_img.webp"/>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

export class ProfileBIOElement extends ProfileElement {
    constructor() {
        super(ElementType.BIO);

        this.body = /*html*/ `
            <div class="profile-element" data-type="${ElementType.BIO}">
                <div class="element-body">
                    <div id="profile-bio-element" class="bio-wrapper">
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
                </div>
            </div>
        `;

        this.inEdit = this.body;
    }
}

/* ------------------------
   Counts and helpers
   ------------------------ */
const CountOnElements = {
    [ElementType.CUSTOM]:          4,
    [ElementType.COMMENT_SECTION]: 1,
    [ElementType.SPOTLIGHT]:       4,
    [ElementType.BIO]:             1,
};

function generateId() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    // fallback
    return 'pe-' + Math.random().toString(36).slice(2, 9);
}

/* Render BBCode only inside .element-body to avoid touching the outer wrapper */
function RenderBBCode() {
    $(".profile-element .element-body").each(function () {
        const raw = $(this).html();
        $(this).html(BBCodeRender(raw));
    });
}

/* ------------------------
   Load view mode elements
   ------------------------ */
export function LoadProfileElements(profileDesignJSON) {
    // view containers (unchanged)
    $("#custom-profile-left").empty();
    $("#custom-profile-right").empty();

    profileDesignJSON.elements.left.forEach(element => {
        const parsed = JSON.parse(element);
        // parsed.body is already a full .profile-element wrapper
        const $el = $(parsed.body);
        // keep the serialized representation for roundtrip
        $el.attr('data-element-json', element);
        $("#custom-profile-left").append($el);
    });

    profileDesignJSON.elements.right.forEach(element => {
        const parsed = JSON.parse(element);
        const $el = $(parsed.body);
        $el.attr('data-element-json', element);
        $("#custom-profile-right").append($el);
    });

    RenderBBCode();
}

/* ------------------------
   Drag/drop setup (re-usable)
   ------------------------ */
function setupDragDrop() {
    // ensure each top-level .profile-element has an id
    $(".profile-element").each(function () {
        if (!this.id) this.id = generateId();
    });

    // make draggable
    $(".profile-element").attr("draggable", true);

    // dragstart
    $(".profile-element").off("dragstart").on("dragstart", function (event) {
        event.originalEvent.dataTransfer.setData("id", this.id);
        // allow move
        event.originalEvent.dataTransfer.effectAllowed = 'move';
    });

    // containers
    const containers = "#custom-profile-left-edit, #custom-profile-right-edit, #profile-element-box";

    $(containers).off("dragover").on("dragover", function (event) {
        event.preventDefault();
        event.originalEvent.dataTransfer.dropEffect = 'move';
    });

    $(containers).off("drop").on("drop", function (event) {
        event.preventDefault();
        const id = event.originalEvent.dataTransfer.getData("id");
        const elem = document.getElementById(id);
        if (!elem) return;

        // Determine correct insertion position
        const container = this;
        const mouseY = event.originalEvent.clientY;

        const beforeElement = getDropPositionElement(container, mouseY);

        if (beforeElement && beforeElement !== elem) {
            container.insertBefore(elem, beforeElement);
        } else {
            container.appendChild(elem);
        }
    });
}

/* ------------------------
   Start editing (edit-mode)
   ------------------------ */
export function StartEditingProfile(profileDesignJSON) {
    $("#custom-profile-view").hide();
    $(".custom-profile-editor").show();
    $("#start-editing-profile").hide();

    // Clone the count object properly
    let countOnUnusedElements = { ...CountOnElements };

    // Subtract used elements (left & right) and append their inEdit variant
    $("#custom-profile-left-edit").empty();
    $("#custom-profile-right-edit").empty();

    profileDesignJSON.elements.left.forEach(e => {
        const parsed = JSON.parse(e);
        countOnUnusedElements[parsed.type]--;

        const $node = $(parsed.inEdit);
        // attach the original serialized data so we can rebuild profileDesignJSON later
        $node.attr('data-element-json', e);
        $("#custom-profile-left-edit").append($node);
    });

    profileDesignJSON.elements.right.forEach(e => {
        const parsed = JSON.parse(e);
        countOnUnusedElements[parsed.type]--;

        const $node = $(parsed.inEdit);
        $node.attr('data-element-json', e);
        $("#custom-profile-right-edit").append($node);
    });

    // populate unused pool
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
                // create a jQuery node and store the serialized JSON on it for later
                const serialized = newElement.JSON();
                const $node = $(newElement.inEdit);
                $node.attr('data-element-json', serialized);
                $("#profile-element-box").append($node);
            }

            count--;
        }
    }

    // Render BBCode inside element bodies (do this BEFORE assigning IDs so BBCode doesn't clobber ids)
    RenderBBCode();

    // Remove accidental nested profile-element classes if any (defensive)
    $(".profile-element .profile-element").removeClass("profile-element");

    // setup drag/drop
    setupDragDrop();
}

/* Build a profileDesignJSON from the current editor DOM */
export function CollectProfileDesignJSON() {
    const left = [];
    const right = [];

    $("#custom-profile-left-edit > .profile-element").each(function () {
        const serialized = $(this).attr('data-element-json');
        if (serialized) left.push(serialized);
    });

    $("#custom-profile-right-edit > .profile-element").each(function () {
        const serialized = $(this).attr('data-element-json');
        if (serialized) right.push(serialized);
    });

    return {
        background: { image: "", styling: "" },
        elements: {
            left,
            right
        }
    };
}

/* ------------------------
   End editing and submit
   ------------------------ */
export function EndEditingProfile(button, isAUser, originalProfileJSON) {
    if ($(button).hasClass("submit")) {
        if (isAUser) {
            // collect the updated layout
            const profileJSON = CollectProfileDesignJSON();

            // send to server (serialize as form data just like original code)
            $.ajax({
                url: "./custom_profile_handle.php?isAUser=false",
                method: "POST",
                data: {
                    profile_design: JSON.stringify(profileJSON)
                },
                success: function (response) {
                    alert("Profile update Complete!");
                    location.reload();
                },
                error: function (xhr) {
                    let msg = 'Unknown error';
                    try { msg = JSON.parse(xhr.responseText).error; } catch (e) {}
                    alert("Profile update failed: " + msg);
                }
            });
        }
    }
    else {
        location.reload();
    }
}