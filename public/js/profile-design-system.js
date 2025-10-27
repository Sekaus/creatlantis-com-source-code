/* data structures */

// the list of all the profile design element types
const ProfileDesignElement = {
    // empty profile design slot
    Empty: '',

    // profile ID element
    UserID: {
        IconMode:
                '<div id="profile-id-element" class="profile-design-element post-block icon-mode">' +
                    '<h2 class="design-element-name">UserID</h2>' +
                '</div>',

        DisplayMode:
                '<div id="profile-id-element" class="profile-design-element display-mode">' +
                    '<!-- user ID image and tagline !-->' +
                    '<div class="user-index-0 post-block full-size">' +
                        '<h2>UserID</h2>' +
                        '<img class="profile-image" src="images/default_pp.webp"/>' +
                        '<div class="light-to-dark-shaded user-text-info">' +
                            '<h2 class="user-name">text</h2>' +
                            '<hr/>' +
                            '<div id="more>' +
                                '<p class="land">test</p>' +
                                '<hr/>' +
                                '<p class="hobbies">Test</p>' +
                            '</div>' +
                            '<p class="user-bio">test</p>' +
                        '</div>' +
                    '</div>' +
                '</div>',
        
        CustomHTML: null
    },

    // custom profile design element
    Custom: {
        IconMode:
                '<div class="profile-design-element post-block custom-design-element icon-mode">' +
                    '<h2 class="design-element-name">Custom</h2>' +
                '</div>',

        DisplayMode:
                '<div class="profile-design-element custom-design-element display-mode post-block full-size">' +
                    '<button class="edit-profile-design-element edit" onclick="editProfileElement(this)">Edit</button> <button class="save-profile-design-element submit" onclick="saveProfileElement(this)" style="display: none;">Save</button>' +
                    '<div class="custom-design-element-body">' +
                        '<!-- custom design here !-->' +
                    '</div>' +
                    '<textarea cols="200" rows="20" class="user-bio" name="new_custom_design_element" hidden></textarea>' +
                '</div>',
        
        CustomHTML: '<div><h2>Write some custom HTML and CSS here...</h2></div>'
    },
    
    Spotlight: {
        IconMode:
                '<div class="profile-design-element post-block spotlight-element icon-mode">' +
                    '<h2 class="design-element-name">Spotlight</h2>' +
                '</div>',

        DisplayMode:
                '<div class="profile-design-element spotlight-element display-mode post-block full-size">' +
                    '<button class="edit-profile-design-element edit" onclick="editProfileElement(this, ProfileElementTypes.Spotlight)">Edit</button>' +
                    '<h2>Spotlight</h2>'+
                    '<div class="spotlight-element-body">' +
                        '<!-- spotlight for a post here !-->' +
                    '</div>' +
                '</div>',
        
        CustomHTML: '<img src="images/default_sp.webp"/>'
    },
    
    // comments section element
    /*Comments: {
        IconMode:
                '<div class="profile-design-element post-block custom-design-element icon-mode">' +
                    '<h2 class="design-element-name">Comments</h2>' +
                '</div>',
        
        DisplayMode: '<div id="comment-stack-element" class="post-block"></div>',
        
        CustomHTML: null
    }*/
};

/* drag and drop tool state handle */

// dragAndDropToolIsActive flag
let dragAndDropToolIsActive = false;

// update the drag and drop tool state based on the dragAndDropToolIsActive flag
function updateDragAndDropToolState() {
    if (dragAndDropToolIsActive) {
        // enable drag and drop tool for custom profile design
        $("#profile-design-element-box, #profile-design").sortable("enable");
    } else {
        // desable drag and drop tool for custom profile design
        $("#profile-design-element-box, #profile-design").sortable("disable");
    }
}

// show profile design element box
function showProfileDesignElementBox() {
    // edit #profile-design CSS
    $("#profile-design").css({
        "padding": "auto"
    });
    $("#profile-design").addClass("post-block");

    // edit #profile-design-flex-box CSS
    $("#profile-design-flex-box").css({
        "display": "flex",
        "justify-content": "flex-end"
    });

    $("#edit-profile-design").hide();
    $("#save-profile-design").show();
    $("#profile-design-element-box").show();
}

// hide profile design element box
function hideProfileDesignElementBox() {
    // edit #profile-design CSS
    $("#profile-design").css({
        "padding": "25%",
        "padding-top": "0"
    });
    $("#profile-design").removeClass("post-block");

    // edit #profile-design-flex-box CSS
    $("#profile-design-flex-box").css({
        "display": "block"
    });

    $("#edit-profile-design").show();
    $("#save-profile-design").hide();
    $("#profile-design-element-box").hide();
}

/* save and load design elements and layout on profile */

// change the mode of the dropped elements
function changeModeOfDroppedElements(elementArray, mode, stringIDOffset = 0) {
    for (var index = 0; index < elementArray.length; index++) {
        // only do anything if selected slot is not empty
        if (elementArray[index] !== ProfileDesignElement.Empty) {
            var slot = $('div[data-id="' + stringIDs[stringIDOffset] + '"]');
            var newElementName = getDesignElementName(slot);
            var newElement = getDesignElementByName(newElementName);
            newElement = $((mode === "IconMode" ? newElement.IconMode : newElement.DisplayMode));

            // check if the new element is a custom element
            if (newElementName === "Custom")
                newElement.children('.custom-design-element-body').html(elementArray[index].CustomHTML);
            
            // check if the new element is a spotlight element
            if (newElementName === "Spotlight")
                newElement.children('.spotlight-element-body').html(elementArray[index].CustomHTML);

            //set DataID for the new element
            newElement.attr('data-id', stringIDs[stringIDOffset]);

            // replace the selectet slot with the new element 
            slot.replaceWith(newElement.prop('outerHTML'));

            stringIDOffset++;
        }
    }
    return stringIDOffset;
}

// load custom profile design slots
// FIX-ME: not loading any ProfileDesignElement.Comments on profile
function loadCustomProfileDesignSlots(collectionClass, elementArray) {
    for (var index = 0; index < elementArray.length; index++) {
        if (elementArray[index] !== "") {
            var newElement = $(getDesignElementByName(elementArray[index].ElementName).DisplayMode);

            // check if selected slot index is a custom element
            if (elementArray[index].ElementName === "Custom")
                newElement.children('.custom-design-element-body').html(elementArray[index].CustomHTML);
            else if (elementArray[index].ElementName === "Spotlight")
                newElement.children('.spotlight-element-body').html(elementArray[index].CustomHTML);

            //set DataID for new element
            //elementArray[index].DataID = generateUniqueRandomString(8, stringIDs);
            newElement.attr('data-id', generateUniqueRandomString(8, stringIDs));

            // then place it on the user's profile
            $(collectionClass).append(newElement.prop('outerHTML'));
        }
    }
}

/* Save and Edit Profile Elements */

// profile element type enum
const ProfileElementTypes = {
    Custom: 0,
    Spotlight: 1,
}

// edit custom profile element
function editProfileElement(designElement, elementType = ProfileElementTypes.Custom) {
    var parent = $(designElement).parent();
    switch(elementType) {
        case ProfileElementTypes.Custom:
            parent.children('textarea').show();
            parent.children('textarea').text(parent.children('.custom-design-element-body').html());
            parent.children('.custom-design-element-body').hide();
            parent.children('.edit-profile-design-element').hide();
            parent.children('.save-profile-design-element').show();
            break;
        case ProfileElementTypes.Spotlight:
            window.location.href = "select_posts.php?element_index=" + $(parent).index();
            break;
    }
}

// save custom profile element
function saveProfileElement(designElement, elementType = ProfileElementTypes.Custom) {
    var parent = $(designElement).parent();
    switch(elementType) {
        case ProfileElementTypes.Custom:
            parent.children('textarea').hide();
            parent.children('.custom-design-element-body').show();
            parent.children('.custom-design-element-body').html(parent.children('textarea').val());
            parent.children('.edit-profile-design-element').show();
            parent.children('.save-profile-design-element').hide();
            break;
    }
    saveProfileDesignData();
}

/* handle custom HTML and CSS */

// get the design element by name
function getDesignElementByName(name, mode = "") {
    var newElement = ProfileDesignElement.Empty;
    if (name === 'UserID')
        newElement = ProfileDesignElement.UserID;
    else if (name === 'Custom')
        newElement = ProfileDesignElement.Custom;
    else if(name === 'Spotlight')
        newElement = ProfileDesignElement.Spotlight;

    if (mode !== "")
        return newElement[mode];
    else
        return newElement;
    }
            
    // get the name of a design element slot
    function getDesignElementName(slot) {
        var name = "";
        if ($(slot).attr('id') === 'profile-id-element')
            name = "UserID";
        else if ($(slot).hasClass('custom-design-element'))
            name = "Custom";
        else if($(slot).hasClass('spotlight-element'))
            name = "Spotlight";

        return name;
     }

// convert HTML element to JSON
/*function elementToJson(element) {
    const json = {};
    // Add attributes
    const attributes = element.getAttributeNames();
    if (attributes.length > 0) {
        json.attributes = {};
        for (const attribute of attributes) {
            json.attributes[attribute] = element.getAttribute(attribute);
        }
    }

    // Add child elements, text content, and comments
    const children = element.childNodes;
    if (children.length > 0) {
        json.content = [];
        for (const child of children) {
            if (child.nodeType === Node.ELEMENT_NODE) {
                json.content.push(elementToJson(child));
            } else if (child.nodeType === Node.TEXT_NODE) {
                const text = child.textContent.trim();
                if (text !== '') {
                    json.content.push({"text": text});
                }
            } else if (child.nodeType === Node.COMMENT_NODE) {
                const comment = child.textContent.trim();
                if (comment !== '') {
                    json.content.push({"comment": comment});
                }
            }
        }
    } else {
        json.content = null;
    }

    return {[element.tagName.toLowerCase()]: json};
}*/

// convert JSON element to HTML
/*function jsonToElement(json) {
    const tagName = Object.keys(json)[0];
    const data = json[tagName];

    const element = document.createElement(tagName);

    if (data.attributes) {
        for (const attribute in data.attributes) {
            element.setAttribute(attribute, data.attributes[attribute]);
        }
    }

    if (data.content) {
        for (const item of data.content) {
            if (item['text']) {
                const text = document.createTextNode(item['text']);
                element.appendChild(text);
            } else if (item['comment']) {
                const comment = document.createComment(item['comment']);
                element.appendChild(comment);
            } else {
                const childElement = jsonToElement(item);
                element.appendChild(childElement);
            }
        }
    } else if (data.content === null) {
        if (tagName === 'br' || tagName === 'hr') {
            // self-closing tag without content
            element.setAttribute('type', tagName);
        } else {
            // tag without content
            element.appendChild(document.createTextNode(''));
        }
    }

    return element;
}*/