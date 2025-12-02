<?php
  session_start();

  include_once("./user_classes.php");
  include_once("./config.php");
  include_once("./data_handler.php");

  $dh = new DataHandle($dbConfig, $s3Config, S3BotType::readOnly);
  $viewedUser = $dh->getUserInfo($_GET["username"]);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Main</title>
        <?php include_once("./html_elements/head.html"); ?>
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
                    <img src="./images/icons/editProfileIcon.webp" id="start-editing-profile" title="Start editing your profile page" alt="Edit profil icon" class="navigation-icon" hidden/>
                </div>
            </div>

            <!-- Custom profile view here -->

        </div>
        
        <script type="module">
            import { UserMetadata, CommentSection } from "./js/common.js";

            $("#content-map-left-part").prepend(UserMetadata());

            /* Load in viewed user metadata */
            
            $("#content-map-left-part .user-name").text("<?php echo $viewedUser->username(); ?>");
            $("#content-map-left-part .user-tagline").text("<?php echo $viewedUser->tagline(); ?>");
            $("#content-map-left-part .user-icon").attr("src", "<?php echo $dh->GetURLOnSingleFile($viewedUser->profileImage()); ?>");

            /* Load selected profile tap */
            switch ("<?php echo $_GET["tab"]; ?>") {
                case "profile":
                    $("#profile-container").append(CustomProfileView());

                    $("#date-of-birth").text("<?php echo $viewedUser->dateOfBirth(); ?>");
                    $("#gender").text("<?php echo $viewedUser->gender(); ?>");
                    $("#land").text("<?php echo $viewedUser->land(); ?>");

                    $("#show-profile").addClass("selected-nav-tab");
                    break;
                case "gallery":
                    $("#profile-container").append(Gallery());

                    $("#show-gallery").addClass("selected-nav-tab");
                    break;
                case "faves":
                    $("#profile-container").append(Favorites());

                    $("#show-faves").addClass("selected-nav-tab");
                    break;
                case "journals":
                    $("#profile-container").append(Journals());

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

            function CustomProfileView() {
                return /*html*/ `
                    <div id="short-user-description">
                        <label>Date of birth:</label>
                        <time id="date-of-birth">0000-01-01</time>
                        <label>Gender:</label>
                        <span id="gender">Non-binary</span>
                        <label>Land:</label>
                        <span id="land">Denmark</span>
                    </div>

                    <div id="custom-profile-view">
                        <div id="custom-profile-left">
                            <!-- Custom profile elements left side here -->
                        </div>
                        <div id="custom-profile-right">
                            <!-- Custom profile elements right side here -->
                        </div>
                    </div>
                    `;
            }

            function CustomProfileEdit() {
                return /*html*/`
                    <div id="custom-profile-edit">
                        <!-- In use profile elements here -->
                    </div>

                    <div id="profile-layout-icon-container">
                        <div class="vertical-hr"></div>

                        <button id="cancel">Cancel</button>
                        <button class="submit">Submit</button>

                        <div class="vertical-hr"></div>
                    </div>

                    <div id="profile-element-box">
                        <!-- Unusd profile elements here -->
                    </div>
                    `;
            }

            function CustomProfileElement(content = /*html*/`<div class="custom-content">test</div>`) {
                return /*html*/ `
                    <div class="custom-profile-element profile-element">
                    <div class="profile-element-icon-container">
                        <img src="./images/icons/editIcon.webp" id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon"/>
                    </div>
                        ${content}
                    </div>
                `;
            }

            function CommentSectionElement() {
                return /*html*/ `
                    <div id="comment-section-element" class="profile-element">
                        ${CommentSection()}
                    </div>
                `;
            }

            function PostSpotlightElement(imageFile, title) {
                return /*html*/ `
                    <div class="post-spotlight-element profile-element">
                        <div class="profile-element-icon-container">
                            <img src="./images/icons/editIcon.webp" id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon" />
                        </div>
                        <p class="post-spotlight-title big-text">${title}</p>
                        <div class="post-spotlight">
                            ${imageFile}
                        </div>
                    </div>
                `;
            }

            function ProfileBIOElement() {
                return /*html*/ `
                    <div id="profile-bio-element" class="profile-element">
                        <div id="profile-bio">
                            <h1 class="big-text">About me</h1>

                            <div class="bio-content-box">
                                <img class="user-icon" src="./images/default_pp.webp" />
                            </div>

                            <div class="bio-content-box">
                                <div>
                                    <p class="extra-big-text">Name</p>
                                </div>
                            </div>

                            <hr/>

                            <div class="bio-content-box">
                                <label class="big-text">Hobbies</label>
                                <p id="hobbies" class="bio-content">Gameing / Singing / Talking</p>
                            </div>

                            <hr/>

                            <div class="bio-content-box">
                                <label class="big-text">BIO</label>
                                <div id="user-bio" class="bio-content">
                                    <!-- BIO content here -->
                                </div>
                            </div>

                            <hr/>
                        </div>
                    </div>
                `;
            }

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