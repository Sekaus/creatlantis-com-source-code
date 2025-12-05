import { BBCodeRender } from "./text_formatter.js";
import { CommentSection } from "./common.js";

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
                            <img class="post-spotlight-content" src="${imageFile}"/>
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

// Helper: find the child element to insert before based on vertical cursor position
function getDropPositionElement(container, clientY) {
    const children = Array.from(container.querySelectorAll('.profile-element'));
    for (const child of children) {
        const rect = child.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        if (clientY < midpoint) return child;
    }
    return null;
}

function setupDragDrop() {
    // ensure each top-level .profile-element has an id
    $(".profile-element").each(function () {
        if (!this.id) this.id = generateId();
    });

    // make draggable for pointer-based devices (desktop)
    $(".profile-element").attr("draggable", true);

    $(".profile-element").off("dragstart").on("dragstart", function (event) {
        event.originalEvent.dataTransfer.setData("id", this.id);
        event.originalEvent.dataTransfer.effectAllowed = 'move';
        $(this).addClass('dragging');
    });

    $(".profile-element").off("dragend").on("dragend", function (event) {
        $(this).removeClass('dragging');
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

        const container = this;
        const mouseY = event.originalEvent.clientY;
        const beforeElement = getDropPositionElement(container, mouseY);

        if (beforeElement && beforeElement !== elem) {
            container.insertBefore(elem, beforeElement);
        } else {
            container.appendChild(elem);
        }
    });

    // ------------------------
    // Touch support (mobile / touchscreens)
    // Approach:
    //  - on touchstart: create a placeholder and a floating ghost clone
    //  - on touchmove: move the ghost, determine drop container & insertion index, move placeholder
    //  - on touchend/cancel: replace placeholder with original element and clean up
    // ------------------------

    // State for an active touch drag
    let touchState = {
        active: false,
        identifier: null,
        sourceElem: null,
        ghost: null,
        placeholder: null
    };

    // Namespaced handlers so they can be removed cleanly
    $(document).off('.profileTouch');

    // Start touch drag on the element itself
    $(".profile-element").off('touchstart.profileTouch').on('touchstart.profileTouch', function (e) {
        // Only handle single-finger drags
        if (touchState.active) return;

        const touch = e.originalEvent.touches[0];
        if (!touch) return;

        touchState.active = true;
        touchState.identifier = touch.identifier;
        touchState.sourceElem = this;

        // Create a placeholder with same dimensions to reserve space
        const $src = $(this);
        const placeholder = document.createElement('div');
        placeholder.className = 'profile-element placeholder';
        $(placeholder).css({
            height: $src.outerHeight() + 'px',
            margin: $src.css('margin'),
            'box-sizing': 'border-box',
            'border': '2px dashed rgba(0,0,0,0.15)',
            'background': 'rgba(0,0,0,0.02)'
        });

        // Insert placeholder in place of source
        $src.after(placeholder);
        touchState.placeholder = placeholder;

        // Create ghost clone
        const $ghost = $src.clone();
        $ghost.addClass('drag-ghost');
        $ghost.css({
            position: 'fixed',
            left: (touch.clientX - $src.outerWidth() / 2) + 'px',
            top: (touch.clientY - $src.outerHeight() / 2) + 'px',
            width: $src.outerWidth() + 'px',
            'pointer-events': 'none',
            opacity: 0.95,
            'z-index': 99999
        });

        $('body').append($ghost);
        touchState.ghost = $ghost;

        $src.addClass('dragging');

        // Prevent page scroll while dragging
        e.preventDefault();
    });

    // Move ghost and reposition placeholder
    $(document).on('touchmove.profileTouch', function (e) {
        if (!touchState.active) return;

        // Find the touch corresponding to our drag
        let touch = null;
        for (let i = 0; i < e.originalEvent.touches.length; i++) {
            if (e.originalEvent.touches[i].identifier === touchState.identifier) {
                touch = e.originalEvent.touches[i];
                break;
            }
        }
        if (!touch) return;

        // Prevent page scrolling
        e.preventDefault();

        // Move ghost
        touchState.ghost.css({
            left: (touch.clientX - touchState.ghost.outerWidth() / 2) + 'px',
            top: (touch.clientY - touchState.ghost.outerHeight() / 2) + 'px'
        });

        // Determine container under the touch point
        const elUnder = document.elementFromPoint(touch.clientX, touch.clientY);
        const $container = $(elUnder).closest('#custom-profile-left-edit, #custom-profile-right-edit, #profile-element-box');

        if ($container.length) {
            const before = getDropPositionElement($container[0], touch.clientY);
            if (before && before !== touchState.sourceElem) {
                $container[0].insertBefore(touchState.placeholder, before);
            } else {
                $container[0].appendChild(touchState.placeholder);
            }
        }
    });

    // End touch drag
    $(document).on('touchend.profileTouch touchcancel.profileTouch', function (e) {
        if (!touchState.active) return;

        // If our touch is in the changedTouches, finalize
        let ended = false;
        for (let i = 0; i < e.originalEvent.changedTouches.length; i++) {
            if (e.originalEvent.changedTouches[i].identifier === touchState.identifier) {
                ended = true;
                break;
            }
        }
        if (!ended) return;

        // Replace placeholder with source element
        if (touchState.placeholder && touchState.placeholder.parentNode) {
            touchState.placeholder.parentNode.replaceChild(touchState.sourceElem, touchState.placeholder);
        } else if (touchState.sourceElem) {
            // fallback to append to pool
            document.getElementById('profile-element-box').appendChild(touchState.sourceElem);
        }

        // Clean up ghost
        if (touchState.ghost) touchState.ghost.remove();
        $(touchState.sourceElem).removeClass('dragging');

        // Reset state
        touchState = {
            active: false,
            identifier: null,
            sourceElem: null,
            ghost: null,
            placeholder: null
        };
    });
}

/* ------------------------
    Before starting editing element
    ----------------------- */
export function BeforeStartEditingProfileElement() {
  $(".start-editing-profile-icon").on('click', function() {
    const $elementContainer = $(this).closest("[data-element-json]");
    
    switch ($elementContainer.data("type")) {
        case ElementType.CUSTOM:
            try {
                // Robustly read the data
                let elementData = $elementContainer.data('element-json');

                if (elementData === undefined) {
                    const raw = $elementContainer.attr('data-element-json');
                    if (raw) {
                        try {
                        elementData = JSON.parse(raw);
                        } catch (e) {
                        elementData = raw;
                        }
                    }
                }

                // If somehow it is the string "[object Object]", warn and fall back
                if (elementData === "[object Object]") {
                    console.warn("data-element-json contains '[object Object]'. The source code likely used .attr(..., object) instead of JSON.stringify.");
                    // Option: attempt to read a common property, e.g. elementData.html, or show an empty editor
                    elementData = "";
                }

                // Convert to a safe string for editing
                const textForTextarea = (typeof elementData === 'object') ? elementData.body : String(elementData);

                const $textarea = $('<textarea/>').text(textForTextarea).css({"width": "100%", "padding-bottom": "100%"});

                $elementContainer.find(".custom-content").empty().append($textarea);

                $(".start-editing-profile-icon").remove();
            } catch (e) {
                console.error("Error parsing JSON data or replacing HTML:", e);
            }
            
            break;
        case ElementType.SPOTLIGHT:
            var title = $elementContainer.find(".post-spotlight-title").text();
            
            var $spotlightContainer = $elementContainer.find(".post-spotlight-content");
            
            var $post = $spotlightContainer.first();
            
            var postURL;

            if($post.is("img"))
                postURL = $post.attr("src");
            else if($post.is("a"))
                postURL = $post.attr("href");

            $elementContainer.find(".post-spotlight-element").html(/*html*/ `
                    <label>Title: </label><input type="text" value="${title}" class="upload-input"/>

                    <br/>

                    <label>Post URL: </label><input type="text" value="${postURL}" class="upload-input"/>   
                `).addClass("post-spotlight-title").css("display", "block");

            $(".start-editing-profile-icon").remove();

            break;
    }
  });
}


/* ------------------------
   Start editing layout
   ------------------------ */
export function StartEditingProfileLayout(profileDesignJSON) {
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