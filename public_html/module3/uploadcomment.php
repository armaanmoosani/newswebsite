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
$comment = htmlentities($_POST['comment']);
$user_id = $_SESSION['user_id'];
$story_id = htmlentities($_POST['story_id']);
$body = htmlentities($_POST['comment']);
//from wiki
$stmt = $mysqli->prepare("INSERT INTO comments (user_id, story_id, body) VALUES (?, ?, ?)");
if(!$stmt){
    header("Location: failure.html");
    exit;
}
$stmt->bind_param('iis', $user_id, $story_id, $body);
$stmt->execute();
$stmt->close();

header("Location: display.php?id=".$story_id);
exit;
?>