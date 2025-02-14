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
if(isset($_GET['id'])){
    $id = $_GET['id'];
}
else{
    header("Location: failure.html");
    exit;
}
//from wiki
$stmt = $mysqli->prepare("SELECT stories.title, stories.body, stories.user_id, stories.time, stories.link, users.username FROM stories JOIN users ON stories.user_id = users.id  WHERE stories.id = ?");
if (!$stmt) {
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('i', $id); 
$stmt->execute();
$stmt->bind_result($title, $body, $user_id, $timestamp,$url, $username);
if (!$stmt->fetch()) {
    header("Location: failure.html");
    exit;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlentities($title)?></title>
    <link rel="stylesheet" type="text/css" href="display.css">
</head>
<body>
    <h1><?php echo htmlentities($title)?></h1>
    <p class="author">BY <?php echo htmlentities(strtoupper($username))?></p>
    <p class="time">Updated <?php echo htmlentities(date("F j, g:i a", strtotime($timestamp))); ?></p>
    <p class="body"><?php echo htmlentities($body)?></p>
    <?php if(!empty($url)){ ?>
        <a class="url" href="<?php echo htmlentities($url)?>"><?php echo htmlentities($url)?></a>
    <?php } 
    if($_SESSION['user_id'] == $user_id){ ?>
        <form action="deletestory.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
        <input type="hidden" name="story_id" value="<?php echo $id; ?>" />
        <button type="submit" class="deletebtn">&#x274C</button> 
        <!-- hexadecimal utf-8 representation of ❌. source: https://www.w3schools.com/charsets/ref_emoji.asp -->
    </form>
    <?php }
    ?>
    <br><br>
    <h2 class="titlelabel">Comments:</h2>
    <?php
        $stmt = $mysqli->prepare("SELECT comments.body, comments.time, comments.comment_id, users.username, comments.user_id FROM comments JOIN users ON comments.user_id = users.id WHERE comments.story_id = ? ORDER BY comments.time ASC");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($comment, $comment_time, $comment_id, $comment_user, $comment_user_id);

        //some functions below from php manual
        while ($stmt->fetch()) { ?>
            <h3 class="user"><?php echo $comment_user ?></h3>
            <p class="commenttime"> <?php echo htmlentities(date("F j, g:i a", strtotime($comment_time))); ?></p> 
            <p class="comment"><?php echo htmlentities($comment) ?></p>
            <?php
            if($_SESSION['user_id'] == $comment_user_id){ ?>
                <form action="deletecomment.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                    <input type="hidden" name="comment_id" value="<?php echo $comment_id; ?>" />
                    <input type="hidden" name="story_id" value="<?php echo $id; ?>" />
                    <button type="submit" class="deletebtn">&#x274C</button> 
                    <!-- hexadecimal utf-8 representation of ❌. source: https://www.w3schools.com/charsets/ref_emoji.asp -->
                </form>
                <br>
            <?php 
            }
             ?>
    <?php } 
    $stmt->close();
    if(isset($_SESSION['user_id'])){
    ?>
        <form action="uploadcomment.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
            <input type="hidden" name="story_id" value="<?php echo $id; ?>" />
            <p class="commentlabel">Comment:</p>
            <textarea name="comment" required></textarea><br><br>
            <button type="submit">Post</button>
        </form>
    <?php } ?>
</body>
</html>