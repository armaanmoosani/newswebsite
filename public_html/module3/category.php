<?php
    session_start();
    require 'database.php';
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32)); 
    }
    if(isset($_POST['token'])){
        if(!hash_equals($_SESSION['token'], $_POST['token'])){
            die("Request forgery detected");
        }
    }
    if(isset($_GET['category'])){
        $category = htmlentities($_GET['category']);
    }
    
?>