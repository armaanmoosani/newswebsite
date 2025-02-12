<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Story</title>
    <link rel="stylesheet" type="text/css" href="submitstory.css">
</head>
<body>
    <h1>Submit Story</h1><br>
<?php
    session_start();
    require "database.php";
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32)); 
    }
    $categories = ["Technology", "U.S. News", "World News", "Business", "Sports"];
    if(isset($_POST['token'])){
        if(!hash_equals($_SESSION['token'], $_POST['token'])){
            die("Request forgery detected");
        }
    }
    if(isset($_POST['title']) && isset($_POST['body']) && isset($_POST['category'])){
        if(isset($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
        }
        else{
            header("Location: index.php");
            exit;
        }
        $title = $_POST['title'];
        $body = $_POST['body'];
        $category = $_POST['category'];
        if(isset($_POST['url'])){
            $url = $_POST['url'];
        }    
        else{
            $url = null;
        }
        $stmt = $mysqli->prepare("INSERT INTO stories (user_id, title, body, link, category) VALUES (?, ?, ?, ?, ?)");
        if(!$stmt){
            header("Location: failure.html");
            exit;
        }
        $stmt->bind_param('issss', $user_id, $title, $body, $url, $category);
        $stmt->execute();
        $stmt->close();
        header("Location: newssite.php");
        exit;
    }
    if(isset($_GET['loggedin'])){
        $submitstory = htmlentities($_GET['loggedin']);
        if($submitstory == "true"){ ?>
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="POST">
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
                <label class="titlelabel">Title:</label><br><br>
                <input type="text" class="title" name="title" required><br><br>
                <label class="bodylabel">Body:</label><br><br>
                <textarea name="body" class="body" rows="10" cols="33" required></textarea><br><br>
                <?php 
                foreach($categories as $category){ 
                    $id = strtolower(str_replace([' ', '.'], '', $category));?>
                    <input type="radio" name="category" class="category" value="<?php echo htmlentities($category); ?>" id="<?php echo htmlentities($id); ?>" required />
                    <label for="<?php echo htmlentities($id); ?>" class="tag"><?php echo $category; ?></label>
                <?php } ?><br><br>
                <label class="url">Attached Link:</label><br><br>
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