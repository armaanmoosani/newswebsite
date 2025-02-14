<?php 
session_start();
require "database.php";
require "nav.php";
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
if(isset($_POST['token'])){
    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }
}
if(isset($_POST['story_id'])){
    $story_id = (int)$_POST['story_id'];
    $stmt = $mysqli->prepare("delete from stories where id= ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('i', $story_id);
    $stmt->execute();
    $stmt->close();
    header("Location: newssite.php");
    exit;
}