<?php
    // Start the session
    session_start();
    
    include_once './php_functions/mysql_functions/load_content.php';
    include_once './php_functions/mysql_functions/store_data.php';

    // Setup login session
    if(!isset($_SESSION["uuid"]) || $_SESSION["uuid"] == ""){
        /* user has not login */
        $_POST['login_commando'] = "login";
        /* move to login page */
        header("Location: ./login_page.php");
    }
?>

<!-- nav-bar start !-->
        <div id="nav-bar">
            <!-- pages, search field and submit !-->
            <nav>
                <ul>
                    <li>
                        <ul>
                            <!-- logo !-->
                            <li><a href='index.php'><img src='images/TestIcon.webp'/></a></li>
                            
                            <!-- search filters !-->
                            <li id="search-filters">
                                <!-- search field !-->
                                <input type="text" id="search-field" class="filter">
                                
                                <!-- filter options start !-->
                                
                                <label>Show type</label>
                                <select id="filter-options" class="filter">
                                    <option>all</option>
                                    <option>image</option>
                                    <option>journal</option>
                                    <option class="only-on-index">profile</option>
                                </select>
                                
                                <label>Order by</label>
                                <select id="order-options" class="filter">
                                    <option>newest</option>
                                    <option>oldest</option>
                                </select>
                                
                                <!-- filter options end !-->
                            </li>
                            <li class="dropdown">
                                <!-- submit drop-down !-->
                                <button class="dropdown-toggle submit">Submit</button>
                                <ul class="dropdown-content">
                                    <li><a href='./submit.php?post=image'>Image</a></li>
                                    <!-- select theme !-->
                                    <li><a href='./submit.php?post=journal'>Journal</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <ul>
                            
                            <!-- notifications start !-->
                            
                            <i id="nodes">
                                <a href="notes.php"><img src="images/icons/noteIcon.webp"/></a>
                                <var id="note-count"><?php echo countNotes($_SESSION['uuid']); ?></var>
                            </i>
                            
                            <!-- notifications end !-->
                            
                            <li class="user-info-box main-user">
                                <!-- main user id display !-->
                            </li>
                            <li class="dropdown">
                                <!-- settings drop-down !-->
                                <a class="dropdown-toggle">Settings</a>
                                <ul class="dropdown-content">
                                    <li><a href="./edit_user_info.php">Profile sittings</a></li>
                                    <!-- select theme !-->
                                    <li class="dropdown-buttons">
                                        <span>Theme:</span>

                                        <!-- themes !-->
                                        <div>
                                            <!-- dark !-->
                                            <a onclick="switchTheme(Theme.Dark);">Dark</a>
                                            <!-- light !-->
                                            <a onclick="switchTheme(Theme.Light);">Light</a>
                                        </div>
                                    </li>
                                    <li>
                                        <form method="POST" action="php_functions/mysql_functions/login_handler.php">
                                            <button class="action" type="submit" name="login_commando" value="logout">
                                                Log out
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- nav-bar end !-->
        
        <!-- JQuery functions to nav-bar !-->
        <script src="https://code.jquery.com/jquery-3.6.1.js"></script>
        <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
        <script type='text/javascript' src="js/interface.js"></script>
        <!-- JQuery functions to content display !-->
        <script type='text/javascript' src='js/content-handler.js'></script>
        
        <!-- load in the users info !-->
        <script type='text/javascript'>
            var selectedTheme = Theme.Dark;
            switch('<?php echo $_SESSION['color_theme'];?>') {
                case "dark":
                    selectedTheme = Theme.Dark;
                    break;
                case 'light':
                    selectedTheme = Theme.Light;
                    break;
            }            
            
            switchTheme(selectedTheme);
            
            // load main user id
            <?php
                SetupAndLoadUserID('.main-user', $_SESSION['uuid']);
            ?>
            
            /* store filter and search variable data from before and load it on the next page refresh */
            
            filter = '<?php if(isset($_GET['filter'])) echo $_GET['filter']; ?>';
            order = '<?php if(isset($_GET['order'])) echo $_GET['order'];?>';
            search = '<?php if(isset($_GET['search'])) echo $_GET['search']; ?>'
            
            // check if any of the search filters has be set to a value
            // and if it has, start auto fill on the search filters
            if(!(filter === '' && search === ''))
                autoFillSearchFilters();
        </script>
        
        <?php include_once 'read_and_accept_popup.php' ?>

        <?php include_once 'write_note_popup.php';?>