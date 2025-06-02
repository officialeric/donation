
<?php

function isUserExists($email){
    include 'connection.php';

    $sql = "SELECT * FROM `users` WHERE `email` = '$email'";
    $result = mysqli_query($db, $sql);

    return mysqli_num_rows($result) > 0;
}