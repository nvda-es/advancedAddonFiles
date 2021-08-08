<?php
include("config.php");
session_name($session_name);
session_start();
$db=new SQLite3($db_file);
$username=SQLite3::escapeString($_POST['username']);
$password=SQLite3::escapeString($_POST['password']);
$result=$db->query("select * from users where username='".$username."'");
$row=$result->fetchArray(SQLITE3_ASSOC);
$result->finalize();
$db->close();
if ($row && password_verify($password, $row['password'])){
	$_SESSION['id']=$row['id'];
	$_SESSION['username']=$row['username'];
	$_SESSION['fullname']=$row['fullname'];
	$_SESSION['email']=$row['email'];
	$_SESSION['role']=$row['role'];
	$_SESSION['token'] = md5(uniqid(mt_rand(), true));
	header("location: index.php");
}else{
	header("location: index.php?error=1&user=".$_POST['username']);
}
?>