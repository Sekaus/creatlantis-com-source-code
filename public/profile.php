<?php
  session_start();
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
                    {UserMetadata()}

                    <div id="nav-taps" alt="Profile navigation taps">
                        <a id="show-profile" class="tap">Profile</a>
                        <a id="show-gallery" class="tap selected-nav-tap">Gallery</a>
                        <a id="show-faves" class="tap">Favorites</a>
                        <a id="show-journalss" class="tap">Journals</a>
                    </div>

                    <button id="watch-user" title="Start watching this user" class="">Watch</button>
                </div>

                <div id="content-map-right-part">
                    <img src={editProfileIcon} id="start-editing-profile" title="Start editing your profile page"
                        alt="Edit profil icon" class="navigation-icon" hidden />
                </div>
            </div>

            <script>
                CustomProfileView();
            </script>

        </div>
        
        <script src="./js/common.js"></script>
        <script>
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
                        <img src={editIcon} id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon" />
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
                `
            }

            function PostSpotlightElement(imageFile) {
                return /*html*/ `
                    <div class="post-spotlight-element profile-element">
                        <div class="profile-element-icon-container">
                            <img src={editIcon} id="start-editing-profile" title="Start editing this profile element" alt="Edit icon" class="profile-element-icon" />
                        </div>
                        <p class="post-spotlight-title big-text">&{imageFile}</p>
                        <div class="post-spotlight">
                            {post}
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
                return = /*html*/ `
                    <div id="content-view">
                        <!-- Favorites here -->
                    </div>
                    `
            }
        </script>

        <?php include_once("./html_elements/footer.html"); ?>
    </body>
</html>