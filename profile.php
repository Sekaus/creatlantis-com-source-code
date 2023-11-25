<?php 
    session_start();
    
    /* verify that the user is the owner of the profile */

    $isMainUserNotTheOwner = ($_GET['profile_id'] != $_SESSION['uuid']);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Cache-Control" content="no-store, no-cache">
        <meta http-equiv="Pragma" content="no-cache">
        <title>Profile</title>
        <?php include_once './header.php'?>
    </head>
    <body>
        <?php include_once './nav_bar.php';?>
        
        <!-- profile info start !-->
        
        <!-- edit and save buttons for profile content parts !-->
        <div><button id="edit-profile-design" class="edit" onclick="editProfileDesignLayout()">Edit profile layout</button> <button id="save-profile-design" class="submit" onclick="saveProfileDesignLayout()">Save</button> </div>
        
        <!-- nav taps !-->
        <nav id="nav-taps">
            <ul>
                <li class="user-info-box second-user">
                <!-- user display !-->
                </li>
                <li id="show-profile"><a class="<?php if(!isset($_GET['tap']) || $_GET['tap'] == 'show-profile') echo 'selectet-nav-tap'; ?>">Profile</a></li>
                <li id="show-gallery"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-gallery') echo 'selectet-nav-tap'; ?>">Gallery</a></li>
                <li id="show-faves"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-faves') echo 'selectet-nav-tap'; ?>">Faves</a></li>
                <li id="show-comments"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-comments') echo 'selectet-nav-tap'; ?>">Comments</a></li>
            </ul>
        </nav>
        
        <!-- user details !-->
        <div class="light-to-dark-shaded user-details second-user">
            <p class="gender">test</p>
        </div>
        
        <!-- profile content parts start !-->
        <div id="profile-design-flex-box">
            <!-- profile part !-->
            <div id="profile-design" class="profile-content-part">
                <!-- display custom profile design here !-->
            </div>
            
             <!-- profile design element box !-->
             <div id="profile-design-element-box" class="post-block">
                 <!-- profile design elements here !-->
             </div>
        </div>
        
        <?php include_once './progressbar.php'; ?>

         <!-- gallery part !-->
         <div id="profile-gallery" class="profile-content-part" style="display: none;">
             <?php include './loaded_posts_nav.php'; ?>
         </div>
         
         <!-- fave collection part !-->
         <div id="profile-fave" class="profile-content-part" style="display: none;">
             <?php include './loaded_posts_nav.php'; ?>
         </div>
         
         <!-- comments part !-->
         <?php include './comment_stack.php'; ?>
        
        <!-- profile content parts end !-->
        
        <?php include_once './footer.php';?>
        
        <!-- profile info end !-->
        
        <script src="./js/profile-design-system.js"></script>
        <script>
            // get the profile that the main user is viewing
            mainUserIsOnProfile = '<?php if(isset($_GET['profile_id'])) echo $_GET['profile_id'];?>';
            
            // hide profile design element box
            hideProfileDesignElementBox();
            
            // when the main user clicks on a tap, then change which part of the profile is showing
            $('#nav-taps a').click(function() {
                // switch nav tap
                switchNavTapOnProfile($(this).parent().attr('id'));
            });
            
            // get the tap variabel from the url
            if(<?php echo json_encode(isset($_GET['tap'])); ?>)
                switchNavTapOnProfile(<?php if (isset($_GET['tap']) && $_GET['tap']) echo ('"' . $_GET['tap'] . '"'); else '' ?>);
            
            // switch nav tap
            // FIX-ME: comments section don't show up
            function switchNavTapOnProfile(tapID) {
                // start by hiding all profile content parts
                $('.profile-content-part').hide();
                
                // then show a certain part of the profile based on which tap is clicked
                switch(tapID) {
                    case 'show-profile':
                        $('#profile-design').show();
                        break;
                    case 'show-gallery':
                        $('#comment-stack').remove();
                        //$('.comment-stack-nav').remove();
                        $('#profile-gallery').show();
                        break;
                    case 'show-faves':
                        $('#comment-stack').remove();
                        $('#loaded-comments-nav').remove();
                        //$('.comment-stack-nav').remove();
                        $('#profile-gallery').remove();
                        $('#profile-fave').show();
                        break;
                    //case 'show-comments':
                        //$('profile-comments').show();
                        //break;
                }
            }
            
            <?php
                // get profile design from AWS S3
                $profileDesign = getS3Object($_GET['profile_id'] . "/json/profile_design.json", false);

                // stop false/undefined user for been loaded
                // TO-DO: try make me less hacky
                if(!isset($profileDesign['Body']) && $_SESSION['uuid'] != $_GET['profile_id'])
                    exit('window.location.href = "html_documents/error_pages/error_404_user_not_found.html"; </script>');
                
                // load in comments form profile
                loadComments(-1, $_GET['profile_id'], 10, $commentOffset, isset($_GET['load_times']));
                
                // disply the main user's id in the comment stack
                SetupAndLoadUserID('#comment-stack #add-new-comment', $_SESSION['uuid']);
            ?>
            
            // loaded profileElementArray JSON
            let profileElementArray = <?php
                /* load custom profile design JSON from AWS S3 */
            
                $defaultLayout = '{
                                                "slotArray": [
                                                    ProfileDesignElement.Custom, ProfileDesignElement.UserID,
                                                    ProfileDesignElement.Empty, ProfileDesignElement.Empty,
                                                    ProfileDesignElement.Empty, ProfileDesignElement.Empty
                                                ],
                                                "elementBoxArray" : [
                                                    ProfileDesignElement.Custom,
                                                    ProfileDesignElement.Custom,
                                                    ProfileDesignElement.Custom,
                                                    ProfileDesignElement.Custom,
                                                    ProfileDesignElement.Empty,
                                                    ProfileDesignElement.Empty
                                                ]
                                            }; 
                                            
                                            // save to AWS S3
                                            saveProfileDesignAsJSON();';
                
                /* load slotArray and elementBoxArray */
                if(isset($profileDesign['Body']))
                    echo json_encode(json_decode($profileDesign['Body'])->data);
                else
                    echo $defaultLayout;
            ?>
                        
                        console.log(profileElementArray);
                        
            // load custom profile design elements from JSON file
            loadCustomProfileDesignSlots('#profile-design', profileElementArray.slotArray);
            
            // load unused profile design elements from JSON file
            loadCustomProfileDesignSlots('#profile-design-element-box', profileElementArray.elementBoxArray);
            
            // update the design element's data
            updateDesignElementData();
            
            // setup the drag and drop tool for custom profile design
            $(document).ready(function() {
                $('#profile-design-element-box, #profile-design').sortable({
                        cancel: "#profile-design-element-box span",
                        scroll: false,
                        connectWith: "#profile-design-element-box, #profile-design",
                        start: function(event, ui) {
                            // center the selected element on the mouse cursor
                            $(this).sortable('instance').offset.click = {
                                left: 36,
                                top: 33
                            };
                        }
                 });
                 updateDragAndDropToolState();
            });
            
            // save profile design element data
            //TO-DO: simplyfi me
            function saveProfileDesignData() {
                /* save profile design as JSON */
                
                // convert the profile design's HTML and CSS to JSON
                function convertHTML(elementArray, target) {
                    // convert element layout
                    for (var index = 0; index < elementArray.length; index++) {
                        //var slot = $('div[data-id="' + stringIDs[stringIDOffset] + '"]');
                        var slot = $(target + ' .profile-design-element').eq(index);
                        var element = (slot !== undefined) ? getDesignElementByName(slot) : ProfileDesignElement.Empty;
                        
                        // check if selected element is not empty before moving on to the next stringID index
                        if(element !== ProfileDesignElement.Empty) {
                            // check if element is a custom element
                            var customHTML = null;
                            if(element === ProfileDesignElement.Custom)
                                customHTML = $(jsonToElement(elementToJson(slot.children('.custom-design-element-body').children()[0]))).prop('outerHTML');

                            // directly modify CustomHTML property in profileElementArray
                            if(target === '#profile-design') {
                                profileElementArray.slotArray[index] = {
                                    IconMode: element.IconMode,
                                    DisplayMode: element.DisplayMode,
                                    CustomHTML: customHTML
                                };
                            }
                            else if(target === '#profile-design-element-box') {
                                profileElementArray.elementBoxArray[index] = {
                                    IconMode: element.IconMode,
                                    DisplayMode: element.DisplayMode,
                                    CustomHTML: customHTML
                                };
                            }
                        }
                        else {
                            if(target === '#profile-design')
                                profileElementArray.slotArray[index] = ProfileDesignElement.Empty;
                            else if(target === '#profile-design-element-box')
                                profileElementArray.elementBoxArray[index] = ProfileDesignElement.Empty;
                        }
                    }
                }
                
                // assign the modified arrays back to profileElementArray
                convertHTML(profileElementArray.slotArray, '#profile-design');
                convertHTML(profileElementArray.elementBoxArray, '#profile-design-element-box');
                
                console.log(profileElementArray);
                
                // save to AWS S3
                saveProfileDesignAsJSON();
            }
            
            // save profile design as JSON
            // TO-DO: let me take profileElementArray and data_type as an input
            function saveProfileDesignAsJSON() {
                // show and start the loading screen
                startLoadingScreen()
                
                // save to AWS S3
                $.ajax({
                    url: 'php_functions/s3_functions/uploader_post_actions.php',
                    method: 'POST',
                    data: {
                        data_type: 'profile_design',
                        data: JSON.stringify(profileElementArray)
                    },
                    success: (data) => {
                      console.log(data);
                      // when done, refresh page
                      // TO-DO: fall prove me (make me more unlikeliy to loop forever)
                      location.reload();
                    },
                    error: (xhr, textStatus, error) => {
                      console.error('Request failed. Status code: ' + xhr.status);
                    }
                });
            }
            
            // edit profile design element layout
            function editProfileDesignLayout() {
                dragAndDropToolIsActive = true;
                updateDragAndDropToolState();

                // change the mode of the dropped elements
                var offset = changeModeOfDroppedElements(profileElementArray.slotArray, 'IconMode');
                changeModeOfDroppedElements(profileElementArray.elementBoxArray, 'IconMode', offset);

                // show profile design element box
                showProfileDesignElementBox();

                // update the design element's data
                updateDesignElementData();
            }
            
            // save profile design element layout
            function saveProfileDesignLayout() {
                dragAndDropToolIsActive = false;
                updateDragAndDropToolState();
                
                // change the mode of the dropped elements
                var offset = changeModeOfDroppedElements(profileElementArray.slotArray, 'DisplayMode');
                changeModeOfDroppedElements(profileElementArray.elementBoxArray, 'DisplayMode', offset);
                
                // save profile design element data
                saveProfileDesignData();
            }
            
            // get the design element by string in body of an item
            function getDesignElementByName(item, mode = "") {
                if ($(item).attr('id') === 'profile-id-element')
                    var newElement = ProfileDesignElement.UserID;
               else if ($(item).hasClass('custom-design-element'))
                    var newElement = ProfileDesignElement.Custom;
                else
                    var newElement = ProfileDesignElement.Empty;

                if (mode !== "")
                    return newElement[mode];
                else
                    return newElement;
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
                
                // load all posts made by user
                if(isset($_GET['tap']) && $_GET['tap'] == 'show-gallery')
                    loadContentFromUser($maxKeys, $offset, $_GET['profile_id']);
                
                // load all faved posts from user
                else if(isset($_GET['tap']) && $_GET['tap'] == 'show-faves')
                    loadFavesFromUser($maxKeys, $offset, $_GET['profile_id']);
            ?>
            // hide edit element and layout buttons if the user is not the owner of the profile or if user is not on the profile tap
            if(<?php echo  json_encode(($isMainUserNotTheOwner || (isset($_GET['tap']) && $_GET['tap'] != 'show-profile'))); ?>) {
                $('#edit-profile-design, #save-profile-design, .edit-profile-design-element, .save-profile-design-element').hide();
            }
        </script>
    </body>
</html>