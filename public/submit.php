<?php
    session_start();
    
    include_once 'php_functions/data_filter.php';
    include_once 'php_functions/s3_functions/object_loader.php';
    include_once 'php_functions/mysql_functions/load_content.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Submit</title>
        <?php include_once './header.php' ?>
    </head>
    <body>
        <?php include_once './nav_bar.php' ?>
        <?php 
            // get the post type to submit
            $postType = $_GET['post'];
            $oldImageURL = '';
            
            // if it is a old post that shold be override, then store a ref of its json data and data type
            if(isset($_SESSION['overwrite_post']) && $postType == 'overwrite') {
                $overridePost = getS3Object($_SESSION['overwrite_post']);
                $postType = json_decode($overridePost)->data_type;
                $postTags = loadTagsFromPost($_SESSION['overwrite_post'], true);
                
                // only run this part of the script if it is a old image to overwrite
                if($postType == 'image')
                    $oldImageURL = json_decode($overridePost)->image;
            }
            // if the override post SESSION reqerst still are set, then unset it if it is not needed 
            else if(isset($_SESSION['overwrite_post'])) {
                unset($_SESSION['overwrite_post']);
            }
            // if it is a profile image to replace or upload
            else if($postType == 'profile_image') {
                $dataArray = array (
                    'title' => '',
                    'text' => '',
                );
                
                $overridePost = json_encode($dataArray);
            }
        ?>

        <!--Setup submit page !-->
        <div id="loaded-content">
            <form id='form-post' class="post-block" action="php_functions/s3_functions/uploader_post_actions.php" enctype="multipart/form-data" method="POST">
                <fieldset>
                    <!-- select post type !-->
                    <select name="data_type" id='post-type'>
                        <option value="image"> Submit a new image.</option>
                        <option value="journal"> Submit a new journal.</option>
                        <option value="profile_image" hidden> Change profile image </option>
                    </select>
                    <input name="title" maxlength="99" type="text" placeholder="Title..." required/>
                    <?php
                        // this GET reqerst variable is only set if it is a image from a old post that shold be override 
                        // $_GET[change_image];
                    
                        /* submit image */
                        if ($postType == 'image' || $postType == 'profile_image') {
                            // if it is a new image to submit or if it is a old image to override
                            if($_GET['post'] != 'overwrite' || isset($_GET['change_image'])) {
                                // setup image input
                                echo '<input id="file-input" type="file" name="image" onchange="readURL(this);" accept="image/x-png,image/gif,image/jpeg" required/> <br/>' .
                                     '<p id="invalid-file-mesg" class="post-block" style="display: none;"></p>';
                            }
                            
                            // setup file display
                            echo "<img id='file-display' src='$oldImageURL'/><br/>";
                            
                            // if it is just need to display a old image
                            if ($_GET['post'] == 'overwrite' && !isset($_GET['change_image'])) {
                                // setup change image option
                                echo '<button class="edit" type="button" onclick="startChangingImage()">Change image</button>' . 
                                     '<br/>';
                            }
                        }
                    ?>
                  <textarea name="text" cols="30" rows="2" placeholder="description..."></textarea>
                    
                 <textarea id="tags-input" cols="15" rows="1" type="text" name="tags" maxlength="2250" placeholder="#tags..."/></textarea>
                    
                <button class="submit" value="Submit" onclick="upload()">Submit</button>
            
                </fieldset>
            </form>
        </div>
        
        <?php include_once './progressbar.php'; ?>

        <?php include_once './footer.php';?>
        
        <script>
            // hide search filters
            hideSearchFilters = true;
            
            // store the selected post type option
            var postType = "<?php echo ($postType == NULL ? 'image' : $postType); ?>";
            
            /* set the default of the post type options */
            $('#post-type option[value="' + postType + '"]').attr('selected', 'selected');
            
            /* switch post type to selected post type options */
            $('#post-type').change(function() {
                postType = $(this).val();
                window.location.href=('./submit.php?post=' + postType);
            });
            
            /* when the file in file input change */
            // acceptable file types
            var fileTypes = <?php echo '["' . implode('", "', $acceptableFileTypes) . '"]' ?>;  
            function readURL(input) {
                    if (input.files && input.files[0]) {
                        // file extension from file input
                        var extension = input.files[0].name.split('.').pop().toLowerCase(),
                            // is extension in acceptable types
                            isSuccess = fileTypes.indexOf(extension) > -1;

                        if(isSuccess) {
                        var reader = new FileReader();

                        // display image form file input
                        reader.onload = function (e) {
                            $('#file-display').attr('src', e.target.result);
                            $('#invalid-file-mesg').hide();
                            $('#invalid-file-mesg').html('');
                        };

                        reader.readAsDataURL(input.files[0]);
                    }
                    else {
                        $('#file-display').attr('src', '');
                        $('#invalid-file-mesg').show();
                        $('#invalid-file-mesg').html('ERROR: Invalid file type, only support images of type: .' + fileTypes.toString().replaceAll(',', ', .'));
                    }
                }
            }
        
            // keep tags input syntax clear
            $('#tags-input').change(function() {
                var text = $(this).val().toLowerCase();
                text = text.replace(/^(?!#)/gi, '#');
                text = text.replace(/\W(?!#)/gi, '#');
                text = text.replace(/#(?=#)/gi, '');
                text = text.replace(/#$/gi, '');
                text = text.replace(/# (?=#)/gi, '');
                $(this).val(text);
            });
            
            // disabled submit button clicking and start the upload
            var isUploading = false;
            function upload() {
                // checking each required input for empty value
                var valid = true;
                $('[required]').each(function() {
                    if($.trim($(this).val()) && valid != false)
                        valid = true;
                    else
                        valid = false;
                });

                if(valid && !isUploading) {
                    isUploading = true;
                    
                    // show and start the loading screen
                    startLoadingScreen();
                }
            };
    
            // if it is a old post that shold be override or a profile image that shold be uploaded or replaced, then call auto fill function call
            <?php
                if(isset($overridePost) && ($postType == "image" || $postType == "journal" || $postType == 'profile_image')) 
                    echo "autoFillInput();";
            ?>
            
            // fill the form indputs with old data from the post to override
            function autoFillInput() {
                var json = <?php echo (isset($overridePost) ? $overridePost : "NaN"); ?>;
                
                // check if the data is set
                if(json != "NaN") {
                    // if it is
                    // get the post type
                    if(postType === 'image' || postType === 'profile_image') {
                        // if it is an image to override
                        $('#file-display').attr('src', json.image);
                    }

                    /* fill the text inputs */
                    
                    $('#form-post input[name="title"]').val(json.title);
                    $('#form-post textarea[name="text"]').val(json.text);
                    $('#form-post textarea[name="tags"]').val('<?php echo (isset($postTags) ? $postTags : "NaN"); ?>');
                    
                    // hide the option to change post type
                    $('#post-type').hide();
                    
                    // hide title input fild and tags if it is a profile image to upload or replace
                    if(postType === 'profile_image') {
                        $('#form-post input[name="title"]').val('NaN');
                        $('#form-post input[name="title"]').hide();
                        $('#form-post textarea[name="text"]').hide();
                        $('#form-post textarea[name="tags"]').hide();
                    }
                }
                else
                    // if not
                    console.log("Nothing to auto fill");
            }
            
            // enable the file input so the old image on the post can be changed
            function startChangingImage() {
                // set change_image variable in the URL
                window.location.href = window.location.href + "&change_image";
            }
        </script>
    </body>
</html>