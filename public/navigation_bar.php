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

        <img src="../images/icons/noteIcon.webp" title="Go to your mailbox" alt="Mail icon" class="navigation-icon"/>
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