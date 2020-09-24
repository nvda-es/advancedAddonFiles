<?php
include ("config.php");
$db=new SQLite3($db_file);
if (isset($_GET['file'])) {
	$result=$db->query("select link, downloads from links where file='".SQLite3::escapeString($_GET['file'])."'");
	if ($row = $result->fetchArray(SQLITE3_NUM)){
		$downloads=intval($row[1])+1;
		$db->exec("update links set downloads=".strval($downloads)." where file='".SQLite3::escapeString($_GET['file'])."'");
		header('Location:'.$row[0]);
	}else{
		die("Link not found.");
	}
	$result->finalize();
} else If (isset($_GET['addonslist'])) {
	$addons=array();
	$result=$db->query("select * from addons");
	while ($row=$result->fetchArray(SQLITE3_ASSOC)){
		$row["links"]=array();
		$result2=$db->query("select file, version, channel, minimum, lasttested, link from links where id=".$row['id']);
		while ($link=$result2->fetchArray(SQLITE3_ASSOC)){
			$row['links'][]=$link;
		}
		$result2->finalize();
		$addons[]=$row;
	}
	$result->finalize();
	echo json_encode($addons);
}else{
	echo "<h1>Error:</h1>";
	echo "<p>Please check that the link that brought you here is correct and try again.</p>";
	echo "<p>If you continue to see this message report this error to the developers.</p>";
	echo "<p>Thanks</p>";
}
$db->close();
?>