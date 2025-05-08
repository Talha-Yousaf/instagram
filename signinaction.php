<?php
//variables

$hostname = "bbqserver.mysql.database.azure.com";
$username = "mylogin";
$password = 'TALHAulster"12'; 
$dbname = "videos1";

//connection

$conn = mysqli_connect($hostnamme,$username,$password,$dbname)
        or die("not able to connecr" .mysqli_error($conn));
echo "Connected successfully";

//query

$sql = mysqli_query($conn, "select username,password from users");

//fecth


?>