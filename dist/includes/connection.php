<?php

$server = 'localhost' ;
$user  = 'root';
$password = 'password';
$db_name = 'donation';

//connecting to database
$db = new mysqli($server,$user,$password,$db_name);

//checking the connection
if(!$db){
    echo 'Database connection failed!';
}
