/* load in post blocks */

function loadPost(json, link, fullSize = false) {
    /* load in post form JSON data */
    
    var postBody = '<a href="post_display.php?post_link=' + link + '" class="post-block">';
    
    if(fullSize)
        postBody = '<div class="post-block full-size">';
    
    //load in image post from JSON data
    if(json.data_type === 'image') {        
        if(fullSize) {
            var imageLink = json.image.replace('%2Flow', '%2Fhigh');
            imageLink = imageLink.replace('/low_res.', '/high_res.');
            $('#loaded-content').append(postBody + '<h1>' + json.title + '</h1> <img src="' + imageLink + '" /> <h4>' + json.text + '</h4>');
        }
        else
            $('#loaded-content').append(postBody + '<img src="' + json.image + '" />');
    }
    
    //load in journal post from JSON data
    else if(json.data_type === 'journal') {
        $('#loaded-content').append(postBody + '<div><h1 class="post-title">' + json.title + '</h1><hr/><span>' + json.text); 
    }
    
    //there was an error when trying to load post data from JSON
    else
        $('#loaded-content').append('<p>ERROR');
}

// setup a user info box to display a user's info
function setupUserInfoBox(HTMLTaget, uuid, userIndexOffSet = 0) {
    var infoBoxBody =
            '<a href="./profile.php?profile_id=' + uuid + '">'
                + '<div class="user-id user-index-' + userIndexOffSet + '">'
                    + '<img class="profile-image" src="images/pfp.png"/>'
                    + '<div class="user-text-info">'
                        + '<h2 class="user-name">TestName</h2>'
                        + '<h5 class="user-tagline">Test wait what?</h5>'
                    + '</div>'
                + '</div>'
            + '</a>';

    // setup user info box
    $(HTMLTaget).prepend(infoBoxBody);
}

// load date from post
function loadDate(date) {    
    $('#submit-date').append('Submit date: ' + date);
}

// load tag from post
function loadTag(tag) {  
    var elementBody = '<a href="#" onclick="searchOnIndexPage(' + "'" + tag + "'" +')" class="post-block">';
    
    $('#tags').append(elementBody + tag);
}

// load in user info
function loadUserInfo(HTMLTaget, username, tagline, dateOfBirth, gender, profileImage, mainUserSeeingProfile = false, userIndexOffSet = 0) {
    // put the loaded data about the user profile to show
    var loadedTagetProfileImage = $(HTMLTaget + " .user-index-" + userIndexOffSet + " .profile-image");
    var loadedTagetTextInfo = $(HTMLTaget + " .user-index-" + userIndexOffSet + " .user-text-info");
    
    loadedTagetProfileImage.attr('src', profileImage);
    loadedTagetTextInfo.children('.user-name').text(username);
    loadedTagetTextInfo.children('.user-tagline').text(tagline);
    
    // if the main user is on a users profile, show them the user's details
    if(mainUserSeeingProfile) {
        $(".user-details.second-user").children('.date-of-birth').text(dateOfBirth);
        $(".user-details.second-user").children('.gender').text(gender);
    }
}

// load in user info
function loadUserLogin(email) {
    $('#user-login-info').children('.email').text(email);
    $('#user-login-info').children('.password').text('***************');
}

// load custom profile design from JSON file
function loadCustomProfileDesignSlots(json) {
    json.slotArray.forEach(loadCustomProfileDesignSlot);
}

// load unused profile design elements from JSON file
function loadUnusedProfileDesignElements(json) {
    json.elementBoxArray.forEach(loadUnusedProfileDesignElement);
}

// load custom profile design slot
function loadCustomProfileDesignSlot(slotElement) {
    // check if selected slot index form array is not empty
    if(slotElement !== ProfileDesignElement.Empty) {
        // then place it on the user's profile
        $('#profile-design').append(slotElement.DisplayMode);
    }
}

// load unused profile design elements into profile design element box
function loadUnusedProfileDesignElement(designElement) {
    // check if editMode is true or if selected slot index form array is not empty
    //if(slotElement !== 'empty' || editMode)
    $('#profile-design-element-box').append(designElement.IconMode);
}

// the list of all the profile design element types
const ProfileDesignElement = {
    // empty profile design slot
    Empty: '',
    
    // profile ID element
    UserID:  { 
        IconMode: 
                '<div id="profile-id-element" class="profile-design-element post-block icon-mode">' +
                    '<h2 class="design-element-name">UserID</h2>' +
                '</div>',
        
        DisplayMode:
                '<div id="profile-id-element" class="profile-design-element display-mode">' +
                    '<!-- user ID image and tagline !-->' +
                    '<div class="user-index-0 post-block full-size">' +
                        '<h2>UserID</h2>' +
                        '<img class="profile-image" src="images/pfp.png"/>' +
                        '<div class="light-to-dark-shaded user-text-info">' +
                            '<h2 class="user-name">text</h2>' +
                        '</div>' +
                    '</div>' +
                '</div>'
        },
    
    // custom profile design element
    Custom: {
        IconMode: 
                '<div class="profile-design-element post-block custom-design-element icon-mode">' +
                    '<h2 class="design-element-name">Custom</h2>' +
                '</div>',
        
        DisplayMode:
                '<div class="profile-design-element custom-design-element display-mode post-block full-size">' + 
                      '<div class="custom-design-element-body"> <!-- custom design here !--> </div>' +
                '</div>'
        }
};