/* Load in Post Blocks */

function loadPost(json, link, fullSize = false) {
    /* Load in Post form JSON Data */
    
    var feedback = 
            '<hr/>' + 
            '<div class="post-feedback">' +
                '<!-- star rating !-->' +
                 '<div class="feedback-option" data-id="' + generateRandomString(8) + '">' +
                    '<div class="star-rate">' +
                        '<div class="star-rate-grid">' +
                            '<span class="star" alt="1 star" title="1 star" onmouseover="setRating(this, 1)" onmouseout="' + "setRating(this, 0, 'reload')" + '" onclick="' + "setRating(this, 1, 'store')" + '"> 1 </span>' +
                            '<span class="star" alt="2 stars" title="2 stars" onmouseover="setRating(this, 2)" onmouseout="' + "setRating(this, 0, 'reload')" + '" onclick="' + "setRating(this, 2, 'store')" + '"> 2 </span>' +
                            '<span class="star" alt="3 stars" title="3 stars" onmouseover="setRating(this, 3)" onmouseout="' + "setRating(this, 0, 'reload')" + '" onclick="' + "setRating(this, 3, 'store')" + '"> 3 </span>' +
                            '<span class="star" alt="4 stars" title="4 stars" onmouseover="setRating(this, 4)" onmouseout="' + "setRating(this, 0, 'reload')" + '" onclick="' + "setRating(this, 4, 'store')" + '"> 4 </span>' +
                            '<span class="star" alt="5 stars" title="5 stars" onmouseover="setRating(this, 5)" onmouseout="' + "setRating(this, 0, 'reload')" + '" onclick="' + "setRating(this, 5, 'store')" + '"> 5 </span>' +
                        '</div>' +
                        '<div class="star-rate-feedback-count"><var class="rate-value" title="Current star rate">0</var> / <br/><var class="vote-value" title="Current votes">0</var></div>' +
                    '</div>' +
                 '</div>' +
                 
                 '<!-- add to the favorite collection !-->' +
                 '<button class="feedback-option fave-button" onclick="setFave(this)" title="Fave count" data-id="' + generateRandomString(8) + '">' + 
                    '<img class="fave-icon" src="./images/icons/faveIcon.webp" style="opacity: 0.5;"/>' +
                    '<var class="fave-feedback-count">0</var>' + 
                '</button>' +
                
                '<!-- view count !-->' +
                '<div class="feedback-option" title="View count" data-id="' + generateRandomString(8) + '">' +
                    '<img src="./images/icons/viewIcon.webp"/>' +
                    '<var class="view-feedback-count">0</var>' +
                '</div>' +
                
                '<!-- comment count !-->' +
                '<div class="feedback-option" title="Comment count" data-id="' + generateRandomString(8) + '">' +
                    '<img src="./images/icons/commentIcon.webp"/>' +
                    '<var class="comment-feedback-count">0</var>' +
                '</div>';
    
    var postBody = '<a href="post_display.php?post_link=' + link + '" class="post-block">';
    
    if(fullSize)
        postBody = '<div class="post-block full-size">';
    
    //load in image post from JSON data
    if(json.data_type === 'image') {        
        if(fullSize) {
            var imageLink = json.image.replace('%2Flow', '%2Fhigh');
            imageLink = imageLink.replace('/low_res.', '/high_res.');
            $('#loaded-content').append(postBody + '<h1>' + json.title + '</h1> <img src="' + imageLink + '" /> <div>' + json.text + '</div>' + feedback);
        }
        else
            $('#loaded-content').append(postBody + '<img src="' + json.image + '" /></div>');
    }
    
    //load in journal post from JSON data
    else if(json.data_type === 'journal') {
        if(fullSize)
            $('#loaded-content').append(postBody + '<div><h1 class="post-title">' + json.title + '</h1><hr/><div class="journal-content">' + json.text + '</div></div>' + feedback);
        else
            $('#loaded-content').append(postBody + '<div><h1 class="post-title">' + json.title + '</h1><hr/><div>' + json.text + '</div></div>');
    }
    
    //there was an error when trying to load post data from JSON
    else
        $('#loaded-content').append('<p>ERROR</p>');
}

// setup a user info box to display a user's info
// TO-DO: make user-index- + "value" to its own data value
function setupUserInfoBox(HTMLTaget, uuid, userIndexOffSet = 0) {
    var infoBoxBody =
            '<a href="./profile.php?profile_id=' + uuid + '">'
                + '<div class="user-id user-index-' + userIndexOffSet + '">'
                    + '<img class="profile-image" src="images/default_pp.webp"/>'
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

// load note inbox
function loadNoteInbox(title, date, readed, index) {
    var elementBody = '<li class="post-block" index="' + index + '"><a href="#" onclick="loadNoteText(' + index + '); markNoteAs(true, ' + index + ');">' + title + date + "<b class='read-status'>" + (readed === 1 ? " " : " * ") + "</b>";
    
    $("#note-inbox").append(elementBody);
}

// load in user info
function loadUserInfo(HTMLTaget, username, tagline, land, hobbies, bio, dateOfBirth, gender, profileImage, mainUserSeeingProfile = false, userIndexOffSet = 0) {
    // put the loaded data about the user profile to show
    var loadedTagetProfileImage = $(HTMLTaget + " .user-index-" + userIndexOffSet + " .profile-image");
    var loadedTagetTextInfo = $(HTMLTaget + " .user-index-" + userIndexOffSet + " .user-text-info");
    
    loadedTagetProfileImage.attr('src', profileImage);
    loadedTagetTextInfo.children('.user-name').text(decodeEntities(username));
    loadedTagetTextInfo.children('.user-tagline').text(decodeEntities(tagline));
    
    // if the main user is on a users profile, show them the user's details
    if(mainUserSeeingProfile) {
        $(".user-details.second-user").children('.date-of-birth').text(dateOfBirth + ( (dateOfBirth !== "" && gender !== "")  ? " / " : ""));
        $(".user-details.second-user").children('.gender').text(decodeEntities(gender));
        loadedTagetTextInfo.children('.land').html(decodeEntities(land));
        loadedTagetTextInfo.children('.hobbies').html(decodeEntities(hobbies));
        loadedTagetTextInfo.children('.user-bio').html(decodeEntities(bio));
    }
}

// load in user info visibility
function loadUserInfoVisibility(dateOfBirthVisibility, genderVisibility, landVisibility) {
    // put the loaded data on the users profile to show
    $("#date-of-birth-public").prop('checked', dateOfBirthVisibility === 1 ? true : false);
    $("#gender-public").prop('checked', genderVisibility === 1 ? true : false);
    $("#land-public").prop('checked', landVisibility === 1 ? true : false);
}

// load in user info
function loadUserLogin(email) {
    $('#user-login-info').children('.email').text(email);
    $('#user-login-info').children('.password').text('***************');
}

// replace char at index of a string
String.prototype.replaceAt = function(index, replacement) {
    return this.substring(0, index) + replacement + this.substring(index + (replacement.length > 0 ? replacement.length : 1));
};

// decode HTML entities form json encoded string
function decodeEntities(encodedString) {
    // decode unicode from string
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedString;
    
    // remove " form the start and the end of the string
    /*var lastCharIndex = textArea.value.length - 1;
    textArea.value = textArea.value[0] === '"' ? textArea.value.replaceAt(0, ' ') : textArea.value;
    textArea.value = textArea.value[lastCharIndex] === '"' ? textArea.value.replaceAt(lastCharIndex, ' ') : textArea.value;*/
    
    return textArea.value;
}

// generate a random string of the specified length
function generateRandomString(length) {
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for (var i = 0; i < length; i++) {
      result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}

// generate a unique random string of the specified length
let stringIDs = [];
function generateUniqueRandomString(length, usedStrings) {
    var result = generateRandomString(length);
    
    while (usedStrings.includes(result)) {
      result = generateRandomString(length);
    }
    
    usedStrings.push(result);
    
    return result;
}