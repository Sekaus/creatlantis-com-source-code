<?php
  session_start();

  include_once("./user_classes.php");
  include_once("./config.php");
  include_once("./data_handler.php");

  $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);
  
  $viewedUser = $dh->getUserInfo($_GET["username"]);
  
  $_SESSION["viewed_user"] = $viewedUser->uuid();
  
  $profileDesign = $dh->GetBodyAsStringOnSingleFile($viewedUser->uuid() . "/profile_design.json");
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Main</title>
        <?php include_once("./html_elements/head.html"); ?>
        <script type="module" src="./js/profile.js"></script>
    </head>
    <body>
        <?php include_once("./html_elements/navigation_bar.html"); ?>
        <?php include_once("./setup.php"); ?>

        <div id="profile-container">
            <div id="content-map">
                <div id="content-map-left-part">
                    <!-- User metadata here -->

                    <div id="nav-tabs" alt="Profile navigation tabs">
                        <span id="show-profile" href="" class="tab">Profile</span>
                        <span id="show-gallery" href="" class="tab">Gallery</span>
                        <span id="show-faves" href="" class="tab">Favorites</span>
                        <span id="show-journals" href="" class="tab">Journals</span>
                    </div>

                    <button id="watch-user" title="Start watching this user" class="">Watch</button>
                </div>

                <div id="content-map-right-part">
                    <img src="./images/icons/editProfileIcon.webp" id="start-editing-profile" title="Start editing your profile page layout" alt="Edit profil icon" class="navigation-icon"/>
                </div>
            </div>

            <div id="short-user-description">
                <label>Date of birth:</label>
                <time id="date-of-birth">0000-01-01</time>
                <label>Gender:</label>
                <span id="gender">Non-binary</span>
                <label>Land:</label>
                <span id="land">Denmark</span>
            </div>

            <!-- Custom profile view here -->

        </div>
        
        <script type="module">
            import { UserMetadata } from "./js/common.js";
            import { LoadProfileElements, BeforeStartEditingProfileElement, SaveSingleElement, StartEditingProfileLayout, ElementType, EndEditingProfileLayout, CustomProfileView, CustomProfileEdit, CustomProfileElement, CommentSectionElement, PostSpotlightElement, ProfileBIOElement } from "./js/profile.js";
            
            /* Load in profile elements */

            // Default profile design
            let profileDesignJSON = { 
                background: { image: "", styling: "" }, 
                elements: { 
                    left: [ new CustomProfileElement() ],
                    right: [
                        new ProfileBIOElement(),
                        new CommentSectionElement()
                    ]
                }
            };

            <?php 
                if(isset($profileDesign)) {
                    $decodedJSON = json_decode($profileDesign);
                    
                    $backgroundImage = $decodedJSON->background->image;
                    $backgroundStyle = $decodedJSON->background->styling;

                    $left = json_encode($decodedJSON->elements->left);
                    $right = json_encode($decodedJSON->elements->right);

                    echo "profileDesignJSON = { 
                        background: { image: '$backgroundImage', styling: '$backgroundStyle' }, 
                        elements: { 
                            left: $left,
                            right: $right
                        }
                    }";
                }
            ?>

            $("#profile-container").append(CustomProfileView());
            $("#profile-container").append(CustomProfileEdit());

            $(document).ready(function() {
                LoadProfileElements(profileDesignJSON);
                BeforeStartEditingProfileElement(true);
            })
            
            // Start editing profile layout
            $("#start-editing-profile").click(function() {
                StartEditingProfileLayout(profileDesignJSON);
            });

            // End editing profile layout
            $("#profile-layout-icon-container button").click(function() {
                EndEditingProfileLayout(this, true, profileDesignJSON);
            });

            $(".custom-profile-editor").hide();

            /* Load in viewed user metadata */

            $("#content-map-left-part").prepend(UserMetadata());
            
            $("#content-map-left-part .user-name").text("<?php echo $viewedUser->username(); ?>");
            $("#content-map-left-part .user-tagline").text("<?php echo $viewedUser->tagline(); ?>");
            $("#content-map-left-part .user-icon").attr("src", "<?php echo $dh->GetURLOnSingleFile($viewedUser->profileImage()); ?>");

            /* Load selected profile tap */
            switch ("<?php echo $_GET["tab"]; ?>") {
                case "profile":
                    $("#date-of-birth").text("<?php echo $viewedUser->dateOfBirth(); ?>");
                    $("#gender").text("<?php echo $viewedUser->gender(); ?>");
                    $("#land").text("<?php echo $viewedUser->land(); ?>");

                    $("#show-profile").addClass("selected-nav-tab");
                    break;
                case "gallery":
                    $("#profile-container").append(Gallery());

                    $("#short-user-description").hide();

                    $("#show-gallery").addClass("selected-nav-tab");
                    break;
                case "faves":
                    $("#profile-container").append(Favorites());

                    $("#short-user-description").hide();

                    $("#show-faves").addClass("selected-nav-tab");
                    break;
                case "journals":
                    $("#profile-container").append(Journals());

                    $("#short-user-description").hide();

                    $("#show-journals").addClass("selected-nav-tab");
                    break;
            }

            /* Profile nav taps */

            $(".tab").click(function() {
                if ($(this).is("[id*='show-profile']"))
                    window.location.href = window.location.origin + "/profile/<?php echo $viewedUser->username(); ?>";
                else if ($(this).is("[id*='show-gallery']"))
                    window.location.href = window.location.origin + "/profile/<?php echo $viewedUser->username(); ?>/gallery";
                else if ($(this).is("[id*='show-faves']"))
                    window.location.href = window.location.origin + "/profile/<?php echo $viewedUser->username(); ?>/faves";
                else if ($(this).is("[id*='show-journals']"))
                    window.location.href = window.location.origin + "/profile/<?php echo $viewedUser->username(); ?>/journals";
            });

            /* Other functions */

            function Gallery() {
                return /*html*/ `
                    <div id="gallery">
                        <div id="folder-map">
                            <ol id="folder-filter-list">
                                <li class="selected-folder-filter">filter</li>
                                <li class="">filter</li>
                                <li class="">filter</li>
                            </ol>

                            <br/>

                            <ol id="folder-list">
                                <!-- Folder list here -->
                            </ol>
                        </div>

                        <div id="selected-folder-content-box">
                            <div id="selected-folder-metadata">
                            <p id="selected-folder-name"> Selected folder</p>
                            <var id="selected-folder-content-count" title="The number of items in the selected folder">-999</var>
                            ▼
                            </div>

                            <div id="selected-folder-content">
                                <!-- Selected folder content here -->
                            </div>
                        </div>
                    </div>
                    `;
            }

            function Favorites() {
                return /*html*/ `
                    <div id="content-view">
                        <!-- Favorites here -->
                    </div>
                    `;
            }

            function Journals() {
                return /*html*/ `
                    <div id="content-view">
                        <!-- Journals here -->
                    </div>
                    `;
            }
        </script>

        <?php include_once("./html_elements/footer.html"); ?>
    </body>
</html>