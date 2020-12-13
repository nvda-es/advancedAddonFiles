<?php
$db_file="addons.db";
$session_name="nvdaaddons";
$baseURL="http://myserver/nvdaaddons/";
function logMessage(&$database, $message) {
	$id=$database->query("select count(*) from log")->fetchArray(SQLITE3_NUM)[0];
	$date=date("r");
	$user=$_SESSION['fullname']." (".$_SESSION['email'].")";
	return $database->exec("insert into log (id, date, user, message) values (".$id.", '".$date."', '".$user."', '".SQLite3::escapeString($message)."')");
}
?>