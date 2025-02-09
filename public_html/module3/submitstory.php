<?php
session_start();
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32)); 
}
if(isset($_POST['token'])){
    if(!hash_equals($_SESSION['token'], $_POST['token'])){
        die("Request forgery detected");
    }
}
if(isset($_GET['loggedin'])){
    $submitstory = htmlentities($_GET['loggedin']);
    if($submitstory == "true"){
        header("Location: submitstory.php");
        exit;
    }
    elseif($submitstory=="false"){
        $_SESSION['redirect'] = "submitstory.php";
        header("Location: index.php");
        exit;
    }
    else{
        header("Loaction: failure.html");
        exit;
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Story</title>
</head>
<body>
    
</body>
</html>