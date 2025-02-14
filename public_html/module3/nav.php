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
    if(isset($_POST['logout'])){
        session_destroy();
        header("Location: newssite.php");
        exit;
    }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Moosani Times</title>
    <link rel="stylesheet" type="text/css" href="nav.css">
</head>
<body>
    <!-- syntax for dropdown= https://www.w3schools.com/howto/howto_css_dropdown.asp -->
    <nav>
        <ul>
            <li><a href="newssite.php">Home</a></li>
            <li class="categories"><a href="#">Categories</a>
                <ul class="categories_content">
                    <li><a href="category.php?category=technology">Technology</a></li>
                    <li><a href="category.php?category=us">U.S.</a></li>
                    <li><a href="category.php?category=sports">Sports</a></li>
                    <li><a href="category.php?category=business">Business</a></li>
                    <li><a href="category.php?category=world">World</a></li>
                </ul>
            </li>
            <?php 
            if(isset($_SESSION['user_id'])){ ?>
                <li><a href="submitstory.php">Submit Story</a></li> 
            <?php }
            else{ ?>
                <li>
                <form action="index.php" method="POST">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <button type="submit" name="storynotloggedin" class="link-button">Submit Story</button>
                </form>
                </li>
            <?php } ?>
           <li>
                <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                    <?php if(!isset($_SESSION['user_id'])) { ?>
                        <button name="signup" type="submit" class="logout">Sign in</button>
                    <?php } if(isset($_SESSION['user_id'])) { ?>
                        <button name="logout" type="submit" class="logout">Logout</button>
                    <?php } ?>
                </form>
            </li>
        </ul>
    </nav>
</body>
</html>