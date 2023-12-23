<?php
     /* connect to mySQL */
    $mysqli = new mysqli("localhost", "root", "", "userdb", 3307);

    //did it failed or success?
    if ($mysqli->connect_error) {
        //if failed
        die('Failed to connect: ' . $mysqli->connect_error);
    } 
?>