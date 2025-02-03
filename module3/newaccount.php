<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Account</title>
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <h1>Sign Up</h1>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
        <label>Username</label> <br>
        <input type="text" name="newuser" required>
        <br><br>
        <label>Password</label> <br>
        <input type="password" name="newpassword" required>
        <br><br>
        <label>Confirm Password</label> <br>
        <input type="password" name="confirmpassword" required>
        <input type="hidden" name="token" value="<?php echo $_SESSION['token'];?>" />
        <br><br>
        <button name="newsignup" type="submit">Sign Up</button><br><br>
    </form>
    <?php
    require "database.php";
    if(isset($_POST['token'])){
        if(!hash_equals($_SESSION['token'], $_POST['token'])){
            die("Request forgery detected");
        }
    }
    if(isset($_POST['newsignup'])){
        $newuser = htmlentities($_POST['newuser']);
        $newpassword = trim($_POST['newpassword']);
        $confirmpassword = trim($_POST['confirmpassword']);
        if($newpassword !== $confirmpassword){
            echo "<p class='p1'>Passwords do not match</p>";
        }
        else{
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
            $stmt->bind_param('s', $newuser);
            $stmt->execute();
            $stmt->bind_result($cnt);
            $stmt->fetch();
            $stmt->close();
            if($cnt>0){
                echo "<p class='p1'>User already exists</p>";
                header("Location: index.html");
            }
            else{
                //from wiki
                $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare("INSERT INTO users (username, hashed_password) VALUES (?, ?)");
                if(!$stmt){
                    header("Location: failure.html");
                    exit;
                }
                $stmt->bind_param('ss', $newuser, $hashed_password);
                $stmt->execute();
                $stmt->close();
                header("Location: index.php");
                exit;
            }
        }
    }
?>
</body>
</html>