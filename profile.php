<!DOCTYPE html>
<html>
    <head>
        <title>Profile</title>
        <?php include_once './header.php'?>
    </head>
    <body>
        <?php include_once './nav_bar.php';?>
        <!-- profile info start !-->
        
        <!-- nav taps !-->
        <nav id="nav-taps">
            <ul>
                <li class="user-info-box second-user">
                <!-- user display !-->
                </li>
                <li id="show-profile"><a class="selectet-nav-tap">Profile</a></li>
                <li id="show-gallery"><a class="">Gallery</a></li>
            </ul>
        </nav>
        
        <!-- user details !-->
        <div class="light-to-dark-shaded user-details second-user">
            <p class="gender">test</p>
        </div>
        
        <!-- profile content parts start !-->
        
        <!-- profile part !-->
        <div id="profile-design" class="profile-content-part"> 
            <!-- display custom profile design here !-->
        </div>
        
        <!-- gallery part !-->
        <div id="profile-gallery" class="profile-content-part" style="display: none;">
            <?php include_once './loaded_posts_nav.php'; ?>
        </div>
        
        <!-- profile design element box !-->
        <div id="profile-design-element-box" class="profile-content-part post-block">
            <!-- profile design elements here !-->
        </div>
        
        <!-- profile content parts end !-->
        
        <?php include_once './footer.php';?>
        
        <!-- profile info end !-->
        
        <script>
            // get the profile that the main user is viewing
            mainUserIsOnProfile = '<?php if(isset($_GET['profile_id'])) echo $_GET['profile_id'];?>';
            
            // when the main user clicks on a tap, then change which part of the profile is showing
            $('#nav-taps a').click(function() {
                // start by hiding all profile content parts
                $('.profile-content-part').hide();
                
                // then show a certain part of the profile based on which tap is clicked
                switch($(this).parent().attr('id')) {
                    case 'show-profile':
                        $('#profile-design').show();
                        break;
                    case 'show-gallery':
                        $('#profile-gallery').show();
                        break;
                }
            });
            
            // loaded profileElementArray JSON
            let profileElementArray = {
                "slotArray": [
                    ProfileDesignElement.Empty, ProfileDesignElement.Empty,
                    ProfileDesignElement.Empty, ProfileDesignElement.Empty,
                    ProfileDesignElement.Empty, ProfileDesignElement.Empty
                ],
                "elementBoxArray" : [
                    ProfileDesignElement.UserID,
                    ProfileDesignElement.Custom,
                    ProfileDesignElement.Custom,
                    ProfileDesignElement.Custom,
                    ProfileDesignElement.Custom
                ]
            };
            
            // load custom profile design from JSON file
            loadCustomProfileDesignSlots(profileElementArray, updateDesignElementData);
            
            // load unused profile design elements from JSON file
            loadUnusedProfileDesignElements(profileElementArray, updateDesignElementData);
            
            // drag and drop tool for custom profile design
            $(document).ready(function() {
                $('#profile-design-element-box, #profile-design').sortable({
                    scroll: false,
                    connectWith: "#profile-design-element-box, #profile-design",
                    helper: function(event, ui) {
                        // clone the element and set its width
                        var clone = $(ui).clone();
                        
                        // change the clone to icon mode
                        if(clone.hasClass('display-mode')) {
                            var newElement = getDesignElementByName(clone, 'IconMode');
                            clone.replaceWith(newElement);
                            
                            return newElement;
                        }
                        return clone;
                    },
                    start: function(event, ui) {
                        // center the selected element on the mouse cursor
                        $(this).sortable('instance').offset.click = {
                                left: 36,
                                top: 33
                            };; 
                        
                        // set ui helper css 
                        ui.helper.css({
                            'width': 'auto',
                            'height': 'auto',
                            'z-index': '999'
                        });
                        
                        
                    },
                    beforeStop: function(event, ui) {
                        
                        
                    },
                    // FIX-ME: can't not drag the design elements back to the element box
                    stop: function(event, ui) {
                        // change the mode of the dropped element
                        if(ui.item.hasClass('icon-mode') && (ui.item.parent().attr('id') === 'profile-design'))
                            ui.item.replaceWith(getDesignElementByName(ui.item, 'DisplayMode'));
                        
                        // update the design element's data
                        updateDesignElementData();
                    }
                });
            });
            
            // get the design element by string in body of an item
            function getDesignElementByName(item, mode) {
                if(item.attr('id') === 'profile-id-element')
                    var newElement = ProfileDesignElement.UserID;
                else if(item.hasClass('custom-design-element'))
                    var newElement = ProfileDesignElement.Custom;
                
                return newElement[mode];
            }
            
            // update the design element's data
            function updateDesignElementData() {
                /* load user data on custom profile elements */
                
                // refresh UserID design element
                <?php
                    // load profile id
                    loadUserInfo('#profile-id-element', $_GET['profile_id'], 'true');
                ?>
            }
            
            /* load profile content */
            <?php
                /* load post data submitted to profile and its user */
            
                // setup the user info box for the displayed profile
                SetupAndLoadUserID('#nav-taps .user-info-box.second-user', $_GET['profile_id']);
                
                // load all posts
                loadContentFromUser($maxKeys, $offset, $_GET['profile_id']);
            ?>
        </script>
    </body>
</html>