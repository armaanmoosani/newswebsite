<?php 
session_start(); 
require 'database.php';
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
if(isset($_GET['category'])){
    $category = htmlentities($_GET['category']);
    $stmt = $mysqli->prepare("SELECT id, title, `time`, category FROM stories WHERE category = ? ORDER BY `time` DESC");
    $stmt->bind_param('s', $category);
}
//from wiki
else{
    $stmt = $mysqli->prepare("SELECT id, title, `time`, category FROM stories ORDER BY `time` DESC");
}
if (!$stmt) {
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->execute();
$stmt->bind_result($id, $title, $timestamp, $tag);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Moosani Times</title>
    <link rel="icon" type="image/png" href="/~armaanmoosani/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/~armaanmoosani/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/~armaanmoosani/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/~armaanmoosani/favicon/apple-touch-icon.png" />
    <link rel="manifest" href="/~armaanmoosani/favicon/site.webmanifest" />
    <link rel="stylesheet" type="text/css" href="logout.css">
    <link rel="stylesheet" type="text/css" href="nav.css">
</head>
<body>
    <?php require 'nav.php'; ?>
    <h1> Your news</h1>
    <div class="news-grid">
        <?php   
        while($stmt->fetch()){?>
            <div class="news-item">
                <a class="news-title" href="display.php?id=<?php echo urlencode(htmlentities($id)) ?>"> <?php echo htmlentities($title) ?></a><br><br>
                <p class="time"> <?php echo htmlentities(date("F j, g:i a", strtotime($timestamp))) ?></p> <!--https://www.php.net/manual/en/function.date.php-->
                <a class="tag <?php echo $tag?>" href="newssite.php?category=<?php echo urlencode($tag); ?>">
                <?php echo htmlentities($tag); ?>
                </a>
            </div>
      <?php  }
        ?>
    </div>
    <?php
        if(isset($_POST['token'])){
            if(!hash_equals($_SESSION['token'], $_POST['token'])){
                die("Request forgery detected");
            }
        }
    ?>
</body>
</html>