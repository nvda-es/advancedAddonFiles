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
		// If you run into problems with the line below, try opening the database file and running: "alter table links add modified text;"
		$result2=$db->query("select file, version, channel, minimum, lasttested, link, downloads, modified from links where id=".$row['id']);
		while ($link=$result2->fetchArray(SQLITE3_ASSOC)){
			$row['links'][]=$link;
		}
		$result2->finalize();
		$addons[]=$row;
	}
	$result->finalize();
	echo json_encode($addons);
}else{
	session_name($session_name);
	session_start();
	include("header.php");
	set_title("List of download links");
?>
<p>This page displays download links for all the add-ons registered on the system, so you can easily copy and share them.</p>
<?php
	$result=$db->query("select id, summary from addons order by id desc");
	while ($row=$result->fetchArray(SQLITE3_ASSOC)){
		echo "<h2>".$row['summary']."</h2>";
		$result2=$db->query("select file, version, channel, downloads from links where id=".$row['id']);
		while ($link=$result2->fetchArray(SQLITE3_ASSOC)){
			echo "<h3>Channel ".$link['channel'].", version ".$link['version'].", downloaded ".$link['downloads']." times</h3>";
			echo "<p><a href='get.php?file=".$link['file']."'>".$baseURL."get.php?file=".$link['file']."</a></p>";
		}
		$result2->finalize();
	}
	$result->finalize();
	include("footer.php");
}
$db->close();
?>