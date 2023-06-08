<!DOCTYPE html>
<html>
    <head>
        <title>CreAtlantis.com Demo!</title>
        <?php include_once './header.php' ?>
    </head>
    <body>
        <?php include_once './nav_bar.php'; ?>
        
        <!-- nav taps start !-->
        <nav id="nav-taps" class="index-nav-taps">
            <ul>
                <li id="show-start-page"><a class="selectet-nav-tap">Start Page</a></li>
            </ul>
        </nav>
        <!-- nav taps end !-->

        <?php include_once './loaded_posts_nav.php'; ?>
        
        <?php include_once './footer.php';?>

        <!-- JQuery content !-->
        <script type='text/javascript'>
            /* load content on main page */
            <?php
                // check if the filter option is set to show profiles
                // if it is, then show all profiles but no posts
                // else, show all posts but no profiles
                if($filter == "profile")
                    loadAllProfiles($maxKeys, $offset);
                else
                    loadContentFromAll($maxKeys, $offset);
            ?>
        </script>
    </body>
</html>