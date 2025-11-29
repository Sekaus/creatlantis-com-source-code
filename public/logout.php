<?php
    require_once "./data_handler.php";

    DataHandle::logout();

    header("Location: ./login.php"); 
    exit();
?>