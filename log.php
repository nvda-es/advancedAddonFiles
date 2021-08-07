<?php
include("config.php");
include("header.php");
session_name($session_name);
session_start();
$db=new SQLite3($db_file);
if (isset($_GET['p'])){
	$p=SQLite3::escapeString($_GET['p']);
}else{
	$p=0;
}
$result=$db->query("select count(id) from log");
$total=$result->fetchArray(SQLITE3_NUM)[0];
$result->finalize();
$inf=$p*10;
$showlink=true;
if ($inf>=$total){
	$showlink=false;
	$inf=$total-10;
}
$sup=$inf+10;
if ($sup>$total){
	$sup=$total;
}
$result=$db->query("select date, user, message from log order by id desc limit ".$inf.", 10");
set_title("Activity log");
echo "<p>This page displays the application activity log. Each entry contains the date it was recorded, the username of the person who triggered the logging process, and a description.</p>";
echo "<p>Showing entries ".strval($inf+1)."-".$sup." of ".$total."</p>";
if ($p>0){
echo "<a href='log.php?p=".strval($p-1)."'>Previous page</a><br/>";
}
if (($sup<$total)&&($showlink==true)){
echo "<a href='log.php?p=".strval($p+1)."'>Next page</a><br/>";
}
while ($row=$result->fetchArray(SQLITE3_ASSOC)){
	echo "<h2>".$row['date'].", ".$row['user']."</h2>";
	echo "<p>".htmlspecialchars($row['message'])."</p>";
}
$result->finalize();
$db->close();
include("footer.php");
?>