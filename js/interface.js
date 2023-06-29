/* Dropdown Effect */

// select and store default nav tap 
let selectetNavTap = $('#nav-taps .selectet-nav-tap').parent().attr("id");

// the profile that the main user is on
let mainUserIsOnProfile = '';

// click event handler on the document
$(document).on('click', function (event) {
    var target = $(event.target);

    // if the target is not a dropdown-toggle
    if (target.is('.dropdown-toggle')) {
        $('.dropdown-content').not(target.next('.dropdown-content')).slideUp();
        target.next('.dropdown-content').slideToggle();
    }

    // if the target is a dropdown-toggle
    if (!target.is('.dropdown-toggle'))
        $('.dropdown-content').slideUp();

    // if the target is #nav-taps
    if (target.is('#nav-taps a')) {
        $('#nav-taps a').removeClass("selectet-nav-tap");
        target.addClass('selectet-nav-tap');
        selectetNavTap = $('#nav-taps .selectet-nav-tap').parent().attr("id");
        
        // setup the start of the url
        var url = location.protocol + '//' + location.host + location.pathname;
        
        // set the tap variable in the URL
        if (window.location.href.indexOf("?profile_id=") > -1)
           window.location.href = url + "?profile_id=" + mainUserIsOnProfile + "&tap=" + selectetNavTap;
        else
            window.location.href = url + "?tap=" + selectetNavTap;
    }
});

$('.dropdown-content').slideUp("fast"); // hide dropdown context by default

/* Switch Theme */

// theme enum value
const Theme = {
    Dark: 'dark-theme',
    Light: 'light-theme'
};

// switch theme to theme enum value
function switchTheme(theme) {
    $('body').removeClass();
    $('body').addClass(theme);
}
;

switchTheme(Theme.Dark); //load in default theme

/* Search Engine and Filters */

// when selecting an option from filter options or typeing in the search field
let filter;
let order;
let search;

$('.filter').change(function () {
    // get the search filters value from the inputs
    filter = $('#filter-options').select().val();
    order = $('#order-options').select().val();
    search = $('#search-field').val();

    /* Set the Value of the Filter, Search and Order Variable in the URL */

    // setup the start of the url
    var url = location.protocol + '//' + location.host + location.pathname;

    // check if the main user is viewing a profile
    // and if the main user is not, set filter variable after "?" in the url
    // else set it after profile_id variable in the url
    if (mainUserIsOnProfile === '')
        url += "?filter=" + filter + "&order=" + order;
    else
        url += "?profile_id=" + mainUserIsOnProfile + "&filter=" + filter + "&order=" + order ;
    // check if search is set to a value
    if (search !== "") {
        // encode search before useing it in the url and then, use the encoded search in the url
        url += encodeSearch(search);
    }
    // send user to the url
    window.location.href = url + "&tap=" + selectetNavTap;
});

// encode search before useing it in the url
function encodeSearch(searchInput) {
    var encodedSearch = btoa(searchInput);
    encodedSearch = encodeURIComponent(encodedSearch);
    return "&search=" + encodedSearch;
}

// auto fill selected filter option and search field text input (PS. works best if filter and search value has been set)
function autoFillSearchFilters() {
    $('#filter-options').select().val(filter);
    $('#order-options').select().val(order);
    $('#search-field').val(atob(decodeURIComponent(search)));
}

// go to index page and auto fill the value of search field
function searchOnIndexPage(searchValue) {
    /* set the value of the filter and search variable in the url */

    // send user to the index page and autofill search with searchValue
    window.location.href = "index.php?filter=all&order=newest&search=" + encodeSearch(searchValue);
}

/* Disable Search Engine or Filter Options if Needed */

// if main user is viewing a profile, disable filter option "profile"
$(document).ready(function () {
    if (mainUserIsOnProfile !== '') {
        $('.only-on-index').attr("disabled", true);
    }
});

// if main user is submitting a post or viewing one in full size, then hide the search filters
let hideSearchFilters = false;
$(document).ready(function () {
    if (hideSearchFilters) {
        $('#search-filters').hide();
    }
});

/* Feedback system */

// set the value of a star rate GUI
function setRating(starRateElement, rating, mode = "", votes = 0) {
    // update GUI
    var starRate = $(starRateElement).parent().parent().parent().children('.star-rate');
    var clicked = starRate.hasClass('clicked');
    var feedback = starRate.children('.star-rate-feedback-count');
    var rateValue = feedback.children('.rate-value');
    var voteValue = feedback.children('.vote-value');
    
    // round rating to one decimal
    rating = Math.round(rating * 10) / 10;
    
    // set the gradient color in %
    starRate.css('--gradient-percentage', 'calc(' + (((mode ==="reload") ? parseFloat(rateValue.text()) : rating) * 15 ) + '% + ' + 40 + 'px)');
    
    // check if the mode is set to "store"
    if (mode === "store") {
        rateValue.html(rating);
        if(!clicked) {
            voteValue.html(parseInt(voteValue.text()) + 1);
            starRate.addClass('clicked');
        }
        // update data on database
        updateFeedback('star_rate', rating);
    }
    // check if the mode is set to "load"
    else if (mode === "load") {
        rateValue.html(rating);
        voteValue.html(votes);
    }
}

// set the value of a fave counter GUI
function setFave(faveCounterElement, mode = "store", faveCount = "old", userHasFave = false) {
    // update GUI
    if(mode === "store") {
        $(faveCounterElement).toggleClass('clicked');
        var clicked = $(faveCounterElement).hasClass('clicked');
        var faveCount = ((faveCount === "old") ? parseInt($(faveCounterElement).children('.fave-feedback-count').text()) : faveCount) + (clicked ? 1 : -1);
        
        // update data on database
         updateFeedback('fave', (clicked ? 1 : 0));
    }
    else if(mode === "load") {
        if(userHasFave)
            $(faveCounterElement).toggleClass('clicked');
        var clicked = userHasFave;
        var faveCount = faveCount;
    }

    $(faveCounterElement).children('.fave-feedback-count').text(faveCount);
    $(faveCounterElement).children('.fave-icon').css("opacity", (clicked ? 1 : 0.5));
}

// update feedback on database
function updateFeedback(feedbackType, feedbackValue) {
    // save to AWS S3
    $.ajax({
        url: 'php_functions/mysql_functions/feedback_handler.php',
        method: 'POST',
        data: {
            post_link: (new URLSearchParams(window.location.search).get('post_link')),
            post_feedback_type: feedbackType,
            post_feedback_value: feedbackValue
        },
        success: (data) => {
            console.log(data);
        },
        error: (xhr, textStatus, error) => {
            console.error('Request failed. Status code: ' + xhr.status);
        }
    });
}

// load in feedback from post
function loadFeedback(feedbackJSON, task='post_display_feedback') {
    /* Read Feedback from Post */
    if(task === 'post_display_feedback') {
        // read star rate
        var selectetStarRateElement= $('.star');
        setRating(selectetStarRateElement, feedbackJSON.starRate.rateAverage, "load", feedbackJSON.starRate.voteCount);
        
        // read faves
        var selectetFaveCounterElement = $('.fave-button');
        setFave(selectetFaveCounterElement, "load", feedbackJSON.faves.faveCount, feedbackJSON.faves.userHasFave);

        // read views
        $('.view-feedback-count').text(feedbackJSON.viewCount);
    }
}

/* Comment Stack */

// load a comment or reply
function loadComment(comment, date, commentUUID, replyUUID = "") {
    // setup a new comment element
    var commentElement = '<div class="comment ' + (replyUUID !== '' ? 'reply  ' : '') + 'post-block" data-id="' + commentUUID +  '">' 
            + '<p class="comment-text">' + comment + '</p><time>' + date + '</time></div>';
    
    // add the comment element to the comment stack and the target comment (if it is a reply)
    if(replyUUID === '')
        $('#comment-stack').append(commentElement);
    else
        $('#comment-stack .comment[data-id="' + replyUUID+ '"]').append(commentElement);
}

// add a comment or reply to the comment stack
function addComment(fromUUID, toType, comment, replyUUID = null) {
    // save as MySQL
    $.ajax({
        url: 'php_functions/mysql_functions/comment_handler.php',
        method: 'POST',
        data: {
            from_uuid: fromUUID,
            to: ((toType === "post" ? new URLSearchParams(window.location.search).get('post_link') : URLSearchParams(window.location.search).get('profile_id'))),
            to_type: toType,
            comment: $(comment).parent().children('textarea').val(),
            reply_uuid: replyUUID
        },
        success: (data) => {
            console.log(data);
            location.reload();
        },
        error: (xhr, textStatus, error) => {
            console.error('Request failed. Status code: ' + xhr.status);
        }
    });
}

/* Start and Update the Progress Bar */
            
function startLoadingScreen() {
    $('#progressbar').show();
    $('.submit').hide();
    $('.edit').hide();
    
    loadingScreen();
}
            
// play the update animation
function loadingScreen() {
    const dots = "";
                
    setTimeout(function () {
        $('#progressbar label').html('Uploading the post please wait' + dots);
                    
        if(dots.length > 10)
            dots = "";
        else
            dots += "*";
                    
        loadingScreen();
    }, 500);
}