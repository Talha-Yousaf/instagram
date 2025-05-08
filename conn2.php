<?php

//variables

$hostname = "bbqserver.mysql.database.azure.com";
$username = "mylogin";
$password = 'TALHAulster"12'; 
$dbname = "videos1";

//connection

$conn = mysqli_connect($hostname, $username,$password, $dbname )
       or die("Not connected");

//query

$sql = "delete from users where username = 'sharjeel'";
 if (!mysqli_query($conn,$sql )) 
 {
    die("Error in delete query" .mysqli_error());
 } 
 echo  "Data has been deleted";     
 mysqli_close($conn)                    




?>