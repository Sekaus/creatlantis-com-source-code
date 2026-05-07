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
    <?php include_once("./navigation_bar.php"); ?>
    <?php include_once("./setup.php"); ?>

    <div id="index-box">
      <ol id="category-list">
        <li class="category-filter selected-category">All</li>
        <li class="category-filter">Drawings</li>
        <li class="category-filter">Paintings</li>
        <li class="category-filter">Other</li>
      </ol>

      <div id="content-view">
        <!-- Post links here -->
      </div>
    </div>
    
    <?php include_once("./html_elements/footer.html"); ?>
    <script type="module">
      import {Global} from "./js/globals.js";
      import {Image, Journal, DisplayLoadedPost, OnPostThumbClick} from "./js/common.js";
      
      const searchChannel = new BroadcastChannel('search_sync');

      export function refreshGallery() {
          const currentPosts = Global.searchData ? Global.searchData.files : [];
          $("#content-view").empty();
          DisplayLoadedPost(currentPosts, "#content-view");
      }

      // Listen for updates from other tabs
      searchChannel.onmessage = (event) => {
          Global.searchData = event.data;
          refreshGallery();
          
          // Optionally update the search bar values to match
          $("[name='search-text']").val(Global.searchData ? Global.searchData.search.text : '');
          $("[name='post-type']").val(Global.searchData ? Global.searchData.search.type : '');
          $("[name='order']").val(Global.searchData ? Global.searchData.search.order : '');

          $(".post").click(function() {
          OnPostThumbClick(this);
        });
      };

      $(document).ready(function() {
        refreshGallery();

        // Filter Toggle
        $(".category-filter").click(function() {
          $(".category-filter").removeClass("selected-category");
          $(this).addClass("selected-category");
          const isNotes = $(this).text() === "Notes";
          $("#note-content").toggle(isNotes);
          $("#watching-content").toggle(!isNotes);
        });
      });
    </script>
  </body>
</html>