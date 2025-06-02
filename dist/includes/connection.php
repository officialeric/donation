<?php

$server = 'localhost' ;
$user  = 'spancial';
$password = '';
$db_name = 'donation';

//connecting to database
$db = new mysqli($server,$user,$password,$db_name);

//checking the connection
if(!$db){
    echo 'Database connection failed!';
}