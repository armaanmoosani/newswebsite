<?php 
    require 'database.php';
    session_start(); 
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32)); 
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Site</title>
    <link rel="stylesheet" type="text/css" href="login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <h1>News</h1>
    <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
        <label>Username</label> <br>
        <input type="text" name="user" required>
        <br><br>
        <label>Password</label> <br>
        <input type="password" name="password" required>
        <br><br>
        <!-- code line below from https://developers.google.com/recaptcha/docs/display -->
        <div class="captcha-container">
            <div class="g-recaptcha" data-sitekey="6LfB-coqAAAAADiLI_G0u-D4BVV-HEwLiLC3JmhU"></div>
        </div>
        <br>
        <button name="login" type="submit">Login</button><br><br>
    </form>
    <form action="newaccount.php" method="GET">
        <button type="submit">Sign Up</button> <br><br>
        <br><br>
    </form>
    <?php
        //from wiki
        if(isset($_POST['token'])){
            if(!hash_equals($_SESSION['token'], $_POST['token'])){
                die("Request forgery detected");
            }
        }
        if(isset($_SESSION['user_id'])){
            header("Location: newssite.php");
            exit;
        }
        if(isset($_POST['login'])){
            //recapthca verify on backend: https://www.geeksforgeeks.org/google-recaptcha-integration-in-php/
            $secret_key = "6LfB-coqAAAAAFGyUcNUV7MhprF6qJ33mWwtUdne"; 
            $recaptcha_response = $_POST['g-recaptcha-response'];
            $url = 'https://www.google.com/recaptcha/api/siteverify?secret='
            . $secret_key . '&response=' . $recaptcha_response;
            $response = file_get_contents($url);
            $response = json_decode($response);
            if (!$response->success) {
                die ("reCAPTCHA verification failed. Please try again.");
            }
            //from wiki
            $stmt = $mysqli->prepare("SELECT COUNT(*), id, hashed_password FROM users WHERE username=?");
            $user = htmlentities(trim($_POST['user']));
            $stmt->bind_param('s', $user);
            $stmt->execute();
            $stmt->bind_result($cnt, $user_id, $pwd_hash);
            $stmt->fetch();
            $pwd_guess = $_POST['password'];
            if($cnt == 1 && password_verify($pwd_guess, $pwd_hash)){
                $_SESSION['user_id'] = $user_id;
                $_SESSION['token'] = bin2hex(random_bytes(32));
                if(isset($_SESSION['redirect'])){
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);
                    header("Location: $redirect");
                    exit;
                }
                header("Location: newssite.php");
                exit;
            } else{
                echo "<p class='p1'>Invalid user or password</p>";
            }
        }
    ?>
</body>
</html>