<?php
session_start();
require "database.php";
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
if(isset($_POST['token'])){
    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }
}
if(isset($_GET['id'])){
    $id = (int) $_GET['id'];
}
elseif(isset($_POST["signup"])){
    header("Location: index.php");
    exit;
}
elseif(isset($_POST["logout"])){
    session_destroy();
    header("Location: newssite.php");
    exit;
}
else{
    header("Location: failure.html");
    exit;
}
//from wiki
$stmt = $mysqli->prepare("SELECT stories.title, stories.body, stories.user_id, stories.time, stories.link, stories.id, users.username FROM stories JOIN users ON stories.user_id = users.id  WHERE stories.id = ?");
if (!$stmt) {
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('i', $id); 
$stmt->execute();
$stmt->bind_result($title, $body, $user_id, $timestamp,$url, $story_id, $username);
if (!$stmt->fetch()) {
    header("Location: failure.html");
    exit;
}
$stmt->close();
if (isset($_POST['edit_story_id']) && isset($_POST['updated_title']) && isset($_POST['updated_body'])) {
    $edit_story_id = (int) $_POST['edit_story_id'];
    $updated_story = htmlentities($_POST['updated_body']);
    $updated_title = htmlentities($_POST['updated_title']);
    if (!isset($_POST['updated_url'])) {
        $stmt = $mysqli->prepare("UPDATE stories SET title = ?, body = ?, `time` = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('ssii',$updated_title, $updated_story, $edit_story_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    elseif(isset($_POST['updated_url'])){
        $updated_url = htmlentities($_POST['updated_url']);
        $stmt = $mysqli->prepare("UPDATE stories SET title = ?, body = ?, link = ?, `time` = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
        if (!$stmt) {
            printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }
        $stmt->bind_param('sssii',$updated_title, $updated_story, $updated_url, $edit_story_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: display.php?id=".$id);
    exit;
}
if (isset($_POST['update_comment_id']) && isset($_POST['updated_comment'])) {
    $update_comment_id = (int) $_POST['update_comment_id'];
    $updated_comment = htmlentities($_POST['updated_comment']);
    $stmt = $mysqli->prepare("UPDATE comments SET body = ?, `time` = CURRENT_TIMESTAMP WHERE comment_id = ? AND user_id = ?");
    if (!$stmt) {
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('sii', $updated_comment, $update_comment_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: display.php?id=".$id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlentities($title)?></title>
    <link rel="stylesheet" type="text/css" href="display.css">
    <link rel="stylesheet" type="text/css" href="nav.css">
</head>
<body>
    <?php
        require "nav.php";
        if(!isset($_POST['edit_story_id'])){ ?>
            <h1><?php echo htmlentities($title)?></h1>
            <p class="author">BY <?php echo htmlentities(strtoupper($username))?></p>
            <p class="time">Updated <?php echo htmlentities(date("F j, g:i a", strtotime($timestamp))); ?></p><br>
            <p class="body"><?php echo htmlentities($body)?></p>
            <?php 
            if(!empty($url)){ ?>
                <a class="url" href="<?php echo htmlentities($url)?>"><?php echo htmlentities($url)?></a><br><br>
            <?php }
            if($_SESSION['user_id'] == $user_id){  ?>
                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) .'?id='.$id;?>" method="POST">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                    <input type="hidden" name="edit_story_id" value="<?php echo $story_id; ?>">
                    <button type="submit">&#x1F58A;</button>
                    <!-- hexadecimal utf-8 representation of 🖊 source: https://www.w3schools.com/charsets/ref_emoji.asp -->
                </form>
                <form action="deletestory.php" method="POST">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                    <input type="hidden" name="story_id" value="<?php echo $id; ?>" >
                    <button type="submit" class="deletebtn">&#x274C;</button> 
                    <!-- hexadecimal utf-8 representation of ❌. source: https://www.w3schools.com/charsets/ref_emoji.asp -->
            </form>
            <?php } ?>
  <?php 
        } 
        elseif(isset($_POST['edit_story_id']) && $_SESSION['user_id'] == $user_id){
            $edit_story_id = (int) $_POST['edit_story_id'];
            unset($_POST['edit_story_id']);
            ?>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) .'?id='.$id;?>" method="POST">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                <input type="hidden" name="edit_story_id" value="<?php echo $edit_story_id; ?>" >
                <textarea name="updated_title" required><?php echo htmlentities($title)?></textarea>
                <p class="author">BY <?php echo htmlentities(strtoupper($username))?></p>
                <p class="time">Updated <?php echo htmlentities(date("F j, g:i a", strtotime($timestamp))); ?></p>
                <textarea name="updated_body" required><?php echo htmlentities($body); ?></textarea><br>
                <?php
                if(!empty($url)){ ?>
                    <textarea name="updated_url" class="url" href="<?php echo htmlentities($url)?>"><?php echo htmlentities($url)?></textarea><br><br>
                <?php } ?>
                <button type="submit">Update</button>
            </form>
    <?php } ?>
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
            if(($comment_user_id == $_SESSION['user_id'])){
                if((!isset($_POST['edit_comment_id']))){?>
                            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) .'?id='.$id;?>" method="POST">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                                <input type="hidden" name="edit_comment_id" value="<?php echo $comment_id; ?>">
                                <button type="submit">&#x1F58A;</button>
                                <!-- hexadecimal utf-8 representation of 🖊 source: https://www.w3schools.com/charsets/ref_emoji.asp -->
                            </form>
                            <form action="deletecomment.php" method="POST">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                                <input type="hidden" name="comment_id" value="<?php echo $comment_id; ?>" >
                                <input type="hidden" name="story_id" value="<?php echo $id; ?>" >
                                <button type="submit" class="deletebtn">&#x274C;</button> 
                                <!-- hexadecimal utf-8 representation of ❌. source: https://www.w3schools.com/charsets/ref_emoji.asp -->
                            </form><br>
                    <!--Comment Editing -->
          <?php }
                elseif(isset($_POST['edit_comment_id'])){ 
                    $edit_comment_id = (int) $_POST['edit_comment_id'];
                    unset($_POST['edit_comment_id']); ?>
                    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) .'?id='.$id;?>" method="POST">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
                        <input type="hidden" name="update_comment_id" value="<?php echo $edit_comment_id; ?>" > 
                        <textarea name="updated_comment" required><?php echo htmlentities($comment); ?></textarea><br>
                        <button type="submit">Update</button>
                    </form>
              <?php }
            }
        }
        ?>
    <?php
    $stmt->close();
    if(isset($_SESSION['user_id'])){
    ?>
        <form action="uploadcomment.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" >
            <input type="hidden" name="story_id" value="<?php echo $id; ?>" >
            <p class="commentlabel">Comment:</p>
            <textarea name="comment" required></textarea><br><br>
            <button type="submit">Post</button>
        </form>
    <?php } ?>
</body>
</html>