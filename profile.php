<?php 
    session_start();
    
    include_once 'php_functions/mysql_functions/store_data.php';
    include_once 'php_functions/mysql_functions/load_content.php';
    
   $isTheMainUserWatching = isTheUserWatching($_SESSION['uuid'], $_GET['profile_id']);
    
    // verify that the user is the owner of the profile
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
        <div><button id="edit-profile-design" class="edit" onclick="editProfileDesignLayout()">Edit profile layout</button> <button id="save-profile-design" class="submit" onclick="saveProfileDesignLayout()">Save</button></div>
        
        <!-- nav taps !-->
        <nav id="nav-taps">
            <ul>
                <li class="user-info-box second-user">
                <!-- user display !-->
                </li>
                <li id="show-profile"><a class="<?php if(!isset($_GET['tap']) || $_GET['tap'] == 'show-profile') echo 'selectet-nav-tap'; ?>">Profile</a></li>
                <li id="show-gallery"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-gallery') echo 'selectet-nav-tap'; ?>">Gallery</a></li>
                <li id="show-faves"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-faves') echo 'selectet-nav-tap'; ?>">Favorites</a></li>
                <li id="show-comments"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-comments') echo 'selectet-nav-tap'; ?>">Comments</a></li>
                <li id="show-more"><a class="<?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-more') echo 'selectet-nav-tap'; ?>">More</a></li>
                <li>
                    <!-- Watch button !-->
                    <button id="watch-button" class="submit" onclick="<?php echo (!$isTheMainUserWatching ? "storeWatch('add_watcher')" : "storeWatch('remove_watcher')");?>"><?php echo (!$isTheMainUserWatching ? "Watch" : "Stop watching");?></button>
                </li>
            </ul>
        </nav>
        
        <!-- user details !-->
        <div id="about-user" class="light-to-dark-shaded">
            <div class="user-details second-user">
                <span class="date-of-birth">test</span> / <span class="gender">test</span> |
            </div>
            <div id="user-statics">
                <span><var id="watch-count">0</var> Watchers</span>
            </div>
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
             <!-- folder list start !-->
             <ul id="folder-list" class="post-block">
                 <li><?php if(!$isMainUserNotTheOwner) {?><a id="add-folder" class="submit" href="submit_folder.php">Add folder</a><?php };?><?php if(isset($_GET['folder']) && $_GET['folder'] != 'all' && !$isMainUserNotTheOwner) {?><button class="action" onclick="deleteFolder()">Remove folder</button><?php };?></li>
                 <li class="selectet-folder"><a href="profile.php?profile_id=<?php echo $_GET['profile_id'];?>&tap=show-gallery&folder=all"><p class="folder-name">All</p></a></li>
             </ul>
             <!-- folder list end !-->
             <div>
                <h1 id="selected-folder-name">All</h1>
                <p id="selected-folder-description">This is a folder...</p>
                <?php if(isset($_GET['folder']) && $_GET['folder'] != 'all' && !$isMainUserNotTheOwner) {?>
                    <a class="submit" href="select_posts.php?folder=<?php echo $_GET['folder'];?>&mode=add_item">Add to folder</a>
                    <a class="action" href="select_posts.php?folder=<?php echo $_GET['folder'];?>&mode=remove_item">Remove from folder</a>
                <?php };?>
                <?php include './loaded_posts_nav.php';?>
             </div>
         </div>
         
         <!-- fave collection part !-->
         <div id="profile-fave" class="profile-content-part" style="display: none;">
             <?php include './loaded_posts_nav.php'; ?>
         </div>
         
         <!-- comments part !-->
         <?php include './comment_stack.php'; ?>
         
         <!-- show more part !-->
         <div id="show-more" style="display: none;">
             <!-- show watchers of profile !-->
             <h1>watchers</h1>
             <div id="loaded-watchers" class="post-block">
             </div>
             
             <!-- show the one that the profile is watching !-->
             <h1>watching</h1>
             <div id="loaded-watching" class="post-block">
             </div>
             
             <?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-more') include './loaded_posts_nav.php'; ?>
         </div>
        
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
                        $('#profile-gallery').show();
                        break;
                    case 'show-faves':
                        $('#comment-stack').remove();
                        $('#loaded-comments-nav').remove();
                        $('#profile-gallery').remove();
                        $('#profile-fave').show();
                        break;
                    case 'show-more':
                        $('#comment-stack').remove();
                        $("div#show-more").show();
                        break;
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
                                                    { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML}, { "ElementName": "UserID" },
                                                    "", "",
                                                    "", "",
                                                    "", "",
                                                    "", "",
                                                ],
                                                "elementBoxArray" : [
                                                    { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML},
                                                    { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML},
                                                    { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML},
                                                    { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML},
                                                    { "ElementName": "Spotlight", "CustomHTML": ProfileDesignElement.Spotlight.CustomHTML},
                                                    { "ElementName": "Spotlight", "CustomHTML": ProfileDesignElement.Spotlight.CustomHTML},
                                                    { "ElementName": "Spotlight", "CustomHTML": ProfileDesignElement.Spotlight.CustomHTML},
                                                    { "ElementName": "Spotlight", "CustomHTML": ProfileDesignElement.Spotlight.CustomHTML},
                                                    "",
                                                    "",
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
            
            // missing desing elements check list
            const ProfileMustHave = [
                {Count: 1, Element: { "ElementName": "UserID" }},
                {Count: 5, Element: { "ElementName": "Custom", "CustomHTML": ProfileDesignElement.Custom.CustomHTML}},
                {Count: 4, Element: { "ElementName": "Spotlight", "CustomHTML": ProfileDesignElement.Spotlight.CustomHTML}}
            ]
            
            // check for missing design elements on profileElementArray
            // NOTE: this may be a bit hacky but it works
            for (const key in ProfileMustHave) {
                var countOfSelected = 0;
                
                profileElementArray.slotArray.forEach((element) => {
                   if(element.ElementName === ProfileMustHave[key].Element.ElementName) 
                       countOfSelected++;
                });
                profileElementArray.elementBoxArray.forEach((element) => {
                   if(element.ElementName === ProfileMustHave[key].Element.ElementName) 
                       countOfSelected++;
                });
                
                for(var i = 0; i < ProfileMustHave[key].Count - countOfSelected; i++)
                    profileElementArray.elementBoxArray.push(ProfileMustHave[key].Element)
            }
                        
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
                        var elementName = getDesignElementName(slot);
                        
                        // check if selected element is not empty before moving on to the next stringID index
                        if(slot.length !== 0) {
                            // check if element is a custom element or a spotlight
                            var customHTML = null;
                            if(elementName === "Custom")
                                customHTML = slot.children('.custom-design-element-body').html();
                            else if(elementName === "Spotlight")
                                customHTML = slot.children('.spotlight-element-body').html();

                            // directly modify the properties in profileElementArray
                            if(target === '#profile-design') {
                                profileElementArray.slotArray[index] = {
                                    ElementName: elementName,
                                    CustomHTML: customHTML
                                };
                            }
                            else if(target === '#profile-design-element-box') {
                                profileElementArray.elementBoxArray[index] = {
                                    ElementName: elementName,
                                    CustomHTML: customHTML
                                };
                            }
                        }
                        else {
                            if(target === '#profile-design')
                                profileElementArray.slotArray[index] = "";
                            else if(target === '#profile-design-element-box')
                                profileElementArray.elementBoxArray[index] = "";
                        }
                    }
                }
                
                // assign the modified arrays back to profileElementArray
                convertHTML(profileElementArray.slotArray, '#profile-design');
                convertHTML(profileElementArray.elementBoxArray, '#profile-design-element-box');
                
                //console.log(profileElementArray);
                
                // save to AWS S3
                saveProfileDesignAsJSON();
            }
            
            // save profile design as JSON
            // TO-DO: let me take profileElementArray and data_type as an input
            function saveProfileDesignAsJSON() {
                // show and start the loading screen
                startLoadingScreen();
                
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
            
            // update the design element's data
            function updateDesignElementData() {
                /* load user data on profile elements */
                
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
                
                // load watch count for profile
                CountWatchers($_GET['profile_id']);
                
                // load all posts made by user
                if(isset($_GET['tap']) && $_GET['tap'] == 'show-gallery') {
                    if(!isset($_GET['folder']) || $_GET['folder'] == 'all')
                        loadContentFromUser($maxKeys, $offset, $_GET['profile_id']);
                    else
                        loadContentFromFolder($maxKeys, $offset, $_GET['folder']);
                    
                    loadFolders($_GET['profile_id'], 10);
                }
                
                // load all faved posts from user
                else if(isset($_GET['tap']) && $_GET['tap'] == 'show-faves')
                    loadFavesFromUser($maxKeys, $offset, $_GET['profile_id']);
                
                // load all watchers_stack data from user
                else if(isset($_GET['tap']) && $_GET['tap'] == 'show-more') {
                    loadWatchersProfiles($maxKeys, $offset, $_GET['profile_id']);
                    loadWatchingProfiles($maxKeys, $offset, $_GET['profile_id']);
                }
            ?>
                                
            // hide edit element and layout buttons if the user is not the owner of the profile or if user is not on the profile tap
            if(<?php echo  json_encode(($isMainUserNotTheOwner || (isset($_GET['tap']) && $_GET['tap'] != 'show-profile'))); ?>)
                $('#edit-profile-design, #save-profile-design, .edit-profile-design-element, .save-profile-design-element').hide();
            
            // hide watch button if user is the owner of the profile
            if(<?php echo  json_encode(!$isMainUserNotTheOwner); ?>)
                $('#watch-button').hide();
            
            // store watch data
            function storeWatch(command) {
                $.ajax({
                        url: 'php_functions/s3_functions/watch_actions.php',
                        method: 'POST',
                        data: {
                            command: command,
                            uuid: "<?php echo $_SESSION['uuid'];?>",
                            watcher_uuid: "<?php echo $_GET['profile_id'];?>"
                        },
                        success: (data) => {
                          console.log(data);
                          // when done, refresh page
                          location.reload();
                        },
                        error: (xhr, textStatus, error) => {
                          console.error('Request failed. Status code: ' + xhr.status);
                        }
                });
         }
         
                // ask user to confirm before deleteing a folder
                function deleteFolder() {
                    if (confirm('Are you sure you want to delet this folder? (can not be undo)'))
                        //delete the folder
                        sendRequestAsAJAX(); 
                }
                
                function sendRequestAsAJAX() {
                    xhr.onreadystatechange = function() {
                        // when getting a response back
                        if (this.readyState === 4 && this.status === 200) {
                            // log response
                            console.log(this.responseText);
                        
                            // reload page
                            location.reload();
                        }
                  };
                  
                  // send folder command by GET reqerst
                  var url = "php_functions/mysql_functions/folder_actions.php?folder=<?php if(isset($_GET['folder'])) echo $_GET['folder'];?>&mode=remove_folder";
                  xhr.open('GET', url, true);
                  xhr.send();
            }
            
            var xhr = new XMLHttpRequest();
            
            // scroll to loaded folders
            <?php if(isset($_GET['load_times'])) {?>
               $(document).ready(function () {
                   // UNSTABLE
                   $('#folder-list').scrollTop(($('#folder-list').outerHeight()-$('#folder-list').innerHeight()) * loadedFolderCount * 18);
                });
            <?php }?>
                
            // scroll to load more folders
            <?php if(isset($_GET['tap']) && $_GET['tap'] == 'show-gallery' && !$atFolderStackEnd) {?>
               let folderOffset = 10;
               $('#folder-list').scroll(function() {
                    if($('#folder-list')[0].scrollHeight - $('#folder-list').scrollTop() <= $('#folder-list').outerHeight()) {
                        window.location.href = location.protocol + '//' + location.host + location.pathname + "?profile_id=<?php echo $_GET['profile_id'] ?>" + "&load_times=" +<?php echo isset($_GET['load_times']) ? $_GET['load_times']+1 : 2;?> + "&tap=show-gallery<?php echo isset($_GET['folder']) ? "&folder=" . $_GET['folder'] : ""?>";
                   }
               });
            <?php }?>
        
            //highlight selectet folder
            <?php if(isset($_GET['folder']) && $_GET['folder'] != 'all') {?>
                $('.selectet-folder').removeClass("selectet-folder");
                $('li a[href*=<?php echo $_GET['folder'];?>]').addClass('selectet-folder');
            <?php };?>
             $('.selectet-folder .folder-name').text($('.selectet-folder .folder-name').text() + " <");
             
            // edit a folder
            function editFolder(folderUUID) {
                // send folderUUID as GET data
                // then go to submit_folder.php
                window.location.href = "./submit_folder.php?edit_folder_uuid=" + folderUUID;
            }
        </script>
    </body>