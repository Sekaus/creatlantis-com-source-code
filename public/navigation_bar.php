<!-- Desktop layout -->
<nav id="desktop-navigation-bar">
    <div id="nav-bar-left">
        <a href="index.php"><img src="../images/TestIcon.webp" alt="Webside logo icon" class="navigation-icon"/></a>
        <?php include("./html_elements/search_console.html"); ?>

        <?php include("./html_elements/qick_submit.html"); ?>
    </div>

    <div id="nav-bar-right">
        <div class="vertical-hr"></div>

        <?php include("./html_elements/main_user_tab.html"); ?>

        <img id="go-to-your-mailbox" src="../images/icons/noteIcon.webp" title="Go to your mailbox" alt="Mail icon" class="navigation-icon"/>
        <var id="inbox-count" title="The content count in the inbox that are marked as not readed">-1</var>
        <p>(<var id="note-count">-1 Notes</var>)</p>
    </div>
</nav>

<!-- Mobile layout -->
<nav id="mobile-navigation-bar">
    <input type="checkbox" id="mobile-menu-toggle"/>

    <div id="nav-bar-top">
        <label for="mobile-menu-toggle">
            <img id="show-more" src="../images/icons/moreIcon.webp" alt="Show more icon" class="navigation-icon"/>
        </label>

        <a href="index.php"><img src="../images/TestIcon.webp" alt="Webside logo icon" class="navigation-icon"/></a>

        <?php include("./html_elements/main_user_tab.html"); ?>

        <?php include("./html_elements/qick_submit.html"); ?>
    </div>

    <div id="mobile-menu">
        <?php include("./html_elements/search_console.html"); ?>
    </div>
</nav>

<!-- Search engine -->
<script type="module">
    import {Global} from "./js/globals.js";

    // Initialize the channel
    const searchChannel = new BroadcastChannel('search_sync');

    function reload(data) {
        $.ajax({
            type: "POST",
            url: "search_data.php",
            data: data,
            success: function(data) {
                Global.searchData = data;
                // Broadcast the data to all other tabs
                searchChannel.postMessage(data);

                console.log("Search data loaded and broadcasted:", data);
                
                // If the current page has a refresh function, call it
                if (typeof refreshGallery === "function") {
                    refreshGallery();
                }
            },
            error: function(error) {
                console.error("Failed to load search data:", error);
            }
        });
    }
    $(document).ready(function() {
        reload(null);

        $(".navigation-bar-input").on("change", function() {
            const $container = $(this).closest(".search-console");
            
            reload({
                type: $container.find('[name="post-type"]').val(),
                text: $container.find('[name="search-text"]').val(),
                order: $container.find('[name="order"]').val()
            });
        });
    });
</script>