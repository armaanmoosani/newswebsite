
<?php
    //from wiki
    $mysqli = new mysqli('localhost', 'phpuser', 'a8ushd742%o43(2jx@*ujsui', 'module3');
    if($mysqli->connect_errno) {
        printf("Connection Failed: %s\n", $mysqli->connect_error);
        exit;
    }
?>
