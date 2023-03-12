        <!-- content start !-->
        
        <div id="loaded-content">
            <!-- content will be loaded here !-->
            <?php
                /* next and previous loaded posts nav system  */
                include_once 'php_functions/mysql_functions/load_content.php';
                
                if(isset($_POST["previous_posts"]) && $_POST["previous_posts"] > 0)
                    $offset -= $maxKeys;
                
                else if(isset($_POST["next_posts"]) && $_POST["next_posts"] < ($postCount - $maxKeys))
                    $offset += $maxKeys;
            ?>
        </div>
        
        <!-- next and previous posts nav !-->
        <form method="post" id="post-nav">
            <button type="submit" name="previous_posts" class="submit"> &#129128; </button>
            
            <p id="post-count-and-offset"></p>
         
            <button type="submit" name="next_posts" class="submit"> &#129130; </button>
        </form>
        
        <!-- content end !-->