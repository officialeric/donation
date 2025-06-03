<?php 
session_start();
include 'connection.php';
include 'services.php';

if(isset($_POST['register'])){

    function validate($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    

    $username = validate($_POST['username']);
    $email = validate($_POST['email']);
    $phone = validate($_POST['phone']);
    $password = validate($_POST['password']);

    if(empty($username)){
        header('location: ../../register.php?error=username is required!');
    }else if(empty($email)){
        header('location: ../../register.php?error=email is required!');
    }else if(empty($phone)){
        header('location: ../../register.php?error=phone is required!');
    }else if(empty($password)){
        header('location: ../../register.php?error=password is required!');
    }else{
        
        if(isUserExists($email)){
            header(header: 'location: ../../register.php?error=User Already Registered , Please Go Login!');
        }else{

            // hashing the password
            $hashed_password = password_hash($password , PASSWORD_DEFAULT);

            $sql = "INSERT INTO users(username,email,phone,password,role) VALUES('$username','$email','$phone','$hashed_password','donor')";
            $result = mysqli_query($db,$sql);

            if($result){
                // Check if there's a donation redirect
                $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';
                if (!empty($redirect)) {
                    header('location: ../../login.php?info=Registered Successfully! Please login to continue.&redirect=' . urlencode($redirect));
                } else {
                    header('location: ../../login.php?info=Registered Successfully! Please login to continue.');
                }
            }else{
                header('location: ../../register.php?error=Something went wrong!');
            }
        }
        
    }
}


if(isset($_POST['login'])){

    function validate($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    

    $email = validate($_POST['email']);
    $password = validate($_POST['password']);

    if(empty($email)){
        header('location: ../../login.php?error=Email is required!');
    }else if(empty($password)){
        header('location: ../../login.php?error=Password is required!');
    }else{

        if(!isUserExists($email)){
            header('location: ../../login.php?error=User Not Exists , Please Go Register!');
        }else{

            $fetch_user = "SELECT *
                            FROM users
                            WHERE email = '$email'";

            $logged_in_user = mysqli_query($db, $fetch_user);

           $user = mysqli_fetch_assoc($logged_in_user);

           if (!password_verify($password, $user['password'])) {
            header('location: ../../login.php?error=Incorrect Password!');
           }else{
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['role'] = $user['role'];

                // Check for redirect parameter
                $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

                if (!empty($redirect)) {
                    header('location: ../../' . $redirect);
                } else {
                    $location = ($_SESSION['role'] == 'admin' ? 'dist/pages/index.php' : 'dist/pages/donor/index.php');
                    header('location: ../../' . $location . '?info=Login successfully');
                }
           }
        }
        
    }
}

if(isset($_POST['logout'])){
    session_unset();
    session_destroy();

    header('location: ../../index.php?info=logout successfully');
}