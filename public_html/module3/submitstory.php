<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Story</title>
</head>
<body>
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
    $categories = ["Technology", "U.S. News", "World News", "Business", "Sports"];
    if(isset($_GET['loggedin'])){
        $submitstory = htmlentities($_GET['loggedin']);
        if($submitstory == "true"){ ?>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                <label>Title:</label><br><br>
                <input type="text" name="title" required><br><br>
                <label>Body:</label><br><br>
                <textarea name="body" rows="10" cols="33" required></textarea><br><br>
                <?php 
                foreach($categories as $category){ ?>
                    <input type="radio" name="category" value="<?php echo htmlentities($category); ?>" id="<?php echo htmlentities($category); ?>" />
                    <label for="<?php echo htmlentities($category); ?>"><?php echo $category; ?></label>
                <?php } ?>
                <label>Attached Link:</label><br><br>
                <input type="url" name="url"><br><br>
                <button name="submit" type="submit">Submit</button><br><br>      
            </form>
        <?php 
        } 
        elseif($submitstory=="false"){
            $_SESSION['redirect'] = "submitstory.php";
            header("Location: index.php");
            exit;
        }
        else{
            header("Location: failure.html");
            exit;
        }
    }
?>
</body>
</html>