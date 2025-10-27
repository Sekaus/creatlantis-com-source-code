<?php
static $acceptableFileTypes = array('gif', 'jpg', 'jpeg', 'pjpeg', 'png', 'svg', 'webp', 'pjpeg');
static $maxFileSize = 1024 * 1024 * 5;

// remove all none-word chars in a string (PS. includeing dots and there for not good to deal with numbers)
function removeAllNoneWordChars($string) {
    return preg_replace('/[\W.]/', '-', $string);
}

// make sure that the file is below the max file size
function keepTheFileSizeBelowMax($file) {
    global $maxFileSize;

    // get the size of the image file in bytes
    $fileSize = filesize($file);

    if ($fileSize > 0) {
        // is the file size below the max file size?
        if ($fileSize > $maxFileSize) {
            /* if not */

            // calculate the size ratio between the file and the max file size
            $fileSizeRatio = $maxFileSize / $fileSize;

            // Resize the image
            return resize($file, (100 * $fileSizeRatio));
        } else
            return false;
    } else
        return false;
}

// resize image
function resize($imagePath, $percent) {
    // Create an Imagick object from the image file
    $imagick = new Imagick($imagePath);

    // Calculate the new image dimensions
    $width = $imagick->getImageWidth();
    $height = $imagick->getImageHeight();
    $newWidth = $width * $percent / 100;
    $newHeight = $height * $percent / 100;

    // Resize the image
    $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);

    // Set the image quality
    $imagick->setImageCompressionQuality($percent);

    // Return the resized image as a string
    if (file_put_contents($imagePath, $imagick))
        return true;
    else
        return false;
}

// filter JavaScript, PHP, form tags and link tags from text
function filterUnwantedCode($string) {
    // define the allowed tags and their allowed attributes
    $allowedTags = array(
        'b', 'i', 'u', 'strong', 'small', 'em', 'br', 'hr', 'p', 'a', 'ul', 'ol', 'li', 'img', 'blockquote',
        'code', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div'
    );

    $allowedAttributes = array(
        'a' => array('href'),
        'img' => array('src')
    );

    // remove unwanted tags
    $string = strip_tags($string, '<' . implode('><', $allowedTags) . '>');

    // remove unwanted attributes from allowed tags
    $string = preg_replace_callback('/<([a-z0-9]+)([^>]*)>/i', function ($matches) use ($allowedAttributes) {
        $tag = strtolower($matches[1]);
        $attributes = $matches[2];

        if (isset($allowedAttributes[$tag])) {
            // filter unwanted attributes
            $attributes = preg_replace_callback('/([a-z0-9]+)\s*=\s*("[^"]*"|\'[^\']*\')/i', function ($attrMatches) use ($allowedAttributes, $tag) {
                $attrName = strtolower($attrMatches[1]);

                if (in_array($attrName, $allowedAttributes[$tag])) {
                    // keep allowed attributes
                    return $attrMatches[0];
                }

                return '';
            }, $attributes);
        } else {
            // remove all attributes except style
            $attributes = preg_replace_callback('/([a-z0-9]+)\s*=\s*("[^"]*"|\'[^\']*\')/i', function ($attrMatches) {
                $attrName = strtolower($attrMatches[1]);

                if ($attrName === 'style') {
                    // keep style attribute
                    return $attrMatches[0];
                }

                return '';
            }, $attributes);
        }

        return "<$tag$attributes>";
    }, $string);

    return $string;
}

// convert slotArray and elementBoxArray for custom profile design
function convertCustomProfileDesign($profileDesignJSON) {
    /* make the JSON file readable for the profile design load */

    $decodedData = json_decode($profileDesignJSON);

    $decodedData->slotArray = makeLoadedDesignElementsReadable($decodedData->slotArray);
    $decodedData->elementBoxArray = makeLoadedDesignElementsReadable($decodedData->elementBoxArray);

    return $decodedData;
}

// make loaded design elements from JSON (decoded) readable by wirte them as a PHP object array
function makeLoadedDesignElementsReadable($elementArray) {
    $phpArray = [];
    foreach ($elementArray as $element) {
        if ($element != "") {
            $newElement = new class() {
                public $ElementName;
                public $CustomHTML;
            };

            $newElement->ElementName = $element->ElementName;
            $newElement->CustomHTML = $element->CustomHTML;
            array_push($phpArray, $newElement);
        } else
            array_push($phpArray, "");
    }
    return $phpArray;
}

// make output string readable for loadComment in JS
function convertQuotesToUnicode($input) {
    $output = '';
    $length = strlen($input);
    $readingCSS = false;

    for ($i = 0; $i < $length; $i++) {
        $char = $input[$i];
        
        // replace all single quotes and double quotes with html unicode 
        if ($char == "'") {
            // check for =' in $input
            if(substr($output, -1) == "=" || $readingCSS) {
                $output .= '"';
                $readingCSS = !$readingCSS;
            }
            else
                $output .= htmlspecialchars($char);
        }
        else
            $output .= $char;
    }

    return trim(preg_replace('/\s+/', ' ', $output));
}
?>