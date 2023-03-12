/* dropdown effect */

// click event handler on the document
$(document).on('click', function (event) {
    var target = $(event.target);
    
    // if the target is not a dropdown-toggle
    if(target.is('.dropdown-toggle')) {
        $('.dropdown-content').not(target.next('.dropdown-content')).slideUp();
        target.next('.dropdown-content').slideToggle();
    }
    
    // if the target is a dropdown-toggle
    if(!target.is('.dropdown-toggle'))
        $('.dropdown-content').slideUp();
    
    // if the target is #nav-taps
    if(target.is('#nav-taps a')) {
        $('#nav-taps a').removeClass("selectet-nav-tap");
        target.addClass('selectet-nav-tap');
    }
});

$('.dropdown-content').slideUp("fast"); // hide dropdown context by default

/* switch theme */

// theme enum value
const Theme = {
    Dark: 'dark-theme',
    Light: 'light-theme'
};

// switch theme to theme enum value
function switchTheme(theme) {
    $('body').removeClass();
    $('body').addClass(theme);
};

switchTheme(Theme.Dark); //load in default theme

/* search engine and filters */

// when selecting an option from filter options or typeing in the search field
let filter;
let order;
let search;
let mainUserIsOnProfile = '';

$('.filter').change(function () {
    // get the search filters value from the inputs
    filter = $('#filter-options').select().val();
    order = $('#order-options').select().val();
    search = $('#search-field').val();
    
    /* set the value of the filter, search and order variable in the url */
    
    // setup the start of the url
    var url = location.protocol + '//' + location.host + location.pathname;
    
    // check if the main user is viewing a profile
    // and if the main user is not, set filter variable after "?" in the url
    // else set it after profile_id variable in the url
    if(mainUserIsOnProfile === '')
        url += "?filter=" + filter + "&order=" + order;
    else 
        url += "?profile_id=" + mainUserIsOnProfile + "&filter=" + filter + "&order=" + order;
    // check if search is set to a value
    if(search !== "") {
        // encode search before useing it in the url and then, use the encoded search in the url
        url += encodeSearch(search);
    }
    // send user to the url
    window.location.href = url;
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

/* disable search engine or filter options if needed */

// if main user is viewing a profile, disable filter option "profile"
$(document).ready(function() {
    if(mainUserIsOnProfile !== '') {
        $('.only-on-index').attr("disabled", true);
    }
});

// if main user is submitting a post or viewing one in full size, then hide the search filters
let hideSearchFilters = false;
$(document).ready(function() {
    if(hideSearchFilters) {
        $('#search-filters').hide();
    }
});