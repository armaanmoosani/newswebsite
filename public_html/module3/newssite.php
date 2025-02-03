<?php 
session_start(); 
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Moosani Times</title>
    <link rel="stylesheet" type="text/css" href="logout.css">
</head>
<body>
    
    <?php
        if(isset($_POST['token'])){
            if(!hash_equals($_SESSION['token'], $_POST['token'])){
                die("Request forgery detected");
            }
        }
        if(!isset($_SESSION['user_id'])){
            echo "<form action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='POST'>
                  <input type='hidden' name='token' value='" . $_SESSION['token'] . "' />
                  <button name='signup' type='submit' class='logout'>Sign in</button>
                  </form>";
        } 
        else {
            echo "<form action='" . htmlentities($_SERVER['PHP_SELF']) . "' method='POST'>
                 <input type='hidden' name='token' value='" . $_SESSION['token'] . "' />
                 <button name='logout' type='submit' class='logout'>Logout</button>
                 </form>";
        }
        if(isset($_POST["signup"])){
            header("Location: index.php");
            exit;
        }
        if(isset($_POST["logout"])){
            session_destroy();
            header("Location: newssite.php");
            exit;
        }
    ?>
</body>
</html>