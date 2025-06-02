<?php
session_start();
include 'connection.php';

if(isset($_POST['update'])){

    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    if (empty($username) || empty($email) || empty($phone)) {
        header("Location: ../pages/profile.php?update=empty");
        exit();
    }
    // Update query
    $query = "UPDATE users SET username='$username', email='$email', phone='$phone' WHERE id='".$_SESSION['user_id']."'";

    if($db->query($query)){
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        header("Location: ../pages/profile.php?update=success");
    } else {
        header("Location: ../pages/profile.php?update=error");
    }
    
} else {
    header("Location: ../pages/profile.php");
}