<?php
include ("config.php");
session_name($session_name);
session_start();
if (isset($_SESSION['username'])){
	$db=new SQLite3($db_file);
	if (!isset($_GET['id'])){
		logMessage($db, "Error: access to download links page without specifying an add-on");
		die("You must specify an add-on identifier in the URL.");
	}
	$addonid=SQLite3::escapeString($_GET['id']);
	$addonexists=$db->query("select id, name from addons where id=".$addonid);
	if ($addoninfo=$addonexists->fetchArray(SQLITE3_NUM)){
		if ($_SESSION['role']=="0"){
			$result=$db->query("select * from permissions where user=".$_SESSION['id']." and addon=".$addonid);
			if (!$result->fetchArray(SQLITE3_NUM)){
				logMessage($db, "Attempt to access an add-on owned by another user without required privileges");
				die("Permission denied. You cannot perform actions on this add-on.");
			}
		}
		$addonname=$addoninfo[1];
		if (isset($_GET['action'])){
			if (!isset($_POST['token']) || $_POST['token'] != $_SESSION['token']){
				die("Invalid CSRF token provided.");
			}
			$file=SQLite3::escapeString($_POST['file']);
			if ($_GET['action']=="edit"){
				$version=SQLite3::escapeString($_POST['version']);
				$channel=SQLite3::escapeString($_POST['channel']);
				$minimum=SQLite3::escapeString($_POST['minimum']);
				$lasttested=SQLite3::escapeString($_POST['lasttested']);
				$link=SQLite3::escapeString($_POST['link']);
				if ($db->query("select file from links where file='".$file."'")->fetchArray(SQLITE3_NUM)){
					logMessage($db, "Updated download information for add-on ".$addonname.". Version ".$version.", channel ".$channel.". ".$_POST['log']);
					// If you run into problems with the line below, try opening the database file and running: "alter table links add modified text;"
					$db->exec("update links set version='".$version."', channel='".$channel."', minimum='".$minimum."', lasttested='".$lasttested."', link='".$link."', downloads=0, modified=datetime('now') where id=".$addonid." and file='".$file."'");
				}else{
					logMessage($db, "Added download information for add-on ".$addonname.". Version ".$version.", channel ".$channel.". ".$_POST['log']);
					// If you run into problems with the line below, try opening the database file and running: "alter table links add modified text;"
					$db->exec("insert into links (id, file, version, channel, minimum, lasttested, link, downloads, modified) values (".$addonid.", '".$file."', '".$version."', '".$channel."', '".$minimum."', '".$lasttested."', '".$link."', 0, datetime('now'))");
				}
			}elseif ($_GET['action']=="delete"){
				$db->exec("delete from links where file='".$file."'");
				logMessage($db, "Removed download information for add-on ".$addonname.". ".$_POST['log']);
			}else{
				logMessage($db, "Attempt to perform an unrecognized action on the system");
				die("Unrecognized action. Please, don't try to change manually the URLs from your web browser address bar. Use the interface provided by this application instead.");
			}
			header("location: addon.php?id=".$addonid);
		}else{
			include ("header.php");
			set_title("Manage add-on download links for ".$addonname);
?>
<p>On this page, you can manage the download links for the selected add-on.</p>
<p><a href="addons.php">Return to add-ons list</a></p>
<h2>List of download links</h2>
<button id="newlink">New download link</button>
<table>
<caption>List of download links</caption>
<thead>
<tr>
<th>Link key</th>
<th>Version</th>
<th>Channel</th>
<th>Minimum NVDA version</th>
<th>Last tested NVDA version</th>
<th>Download URL</th>
<th>Total downloads since last update</th>
<th>Date updated</th>
<th>Edit</th>
<th>Remove</th>
</tr>
</thead>
<tbody>
<?php
// If you run into problems with the line below, try opening the database file and running: "alter table links add modified text;"
$result=$db->query("select file, version, channel, minimum, lasttested, link, downloads, modified from links where id=".$addonid);
while ($row=$result->fetchArray(SQLITE3_NUM)){
	echo "<tr id='".$row[0]."'>\n";
	foreach ($row as $item){
		echo "<td>".$item."</td>\n";
	}
	echo "<td><button>Edit</button></td>\n";
	echo "<td><button>Remove</button></td>\n";
	echo "</tr>\n";
}
$result->finalize();
?>
</tbody>
</table>
<div id="edit-form" style="display:none" role="dialog" aria-labelledby="edit-form-title">
<h2 id="edit-form-title">Create or update download link for this add-on</h2>
<form method="post" action="addon.php?action=edit&id=<?php echo $addonid; ?>" role="form">
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>"/>
<p><label for="file">Add-on key*</label>
<input type="text" required="" aria-required="true" id="file" name="file" title="Short string used to redirect the user to the download link"/></p>
<p><label for="version">Add-on version*</label>
<input type="text" required="" aria-required="true" id="version" name="version"/></p>
<p><label for="channel">Add-on channel*</label>
<input type="text" id="channel" name="channel" required="" aria-required="true" title="stable, dev, lts..."/></p>
<p><label for="minimum">Minimum NVDA version supported*</label>
<input type="text" required="" aria-required="true" id="minimum" name="minimum" title="year.version.0"/></p>
<p><label for="lasttested">Last tested NVDA version*</label>
<input type="text" required="" aria-required="true" id="lasttested" name="lasttested" title="year.version.0"/></p>
<p><label for="link">Full download link*</label>
<input type="url" required="" aria-required="true" id="link" name="link"/></p>
<p><label for="log">Log message</label>
<textarea id="log" name="log" title="Optional log message displayed for this operation"></textarea></p>
<p><input type="submit" value="Submit"/>
<button type="button" onclick="cancelEdit();">Cancel</button></p>
</form>
</div>
<div id="delete-form" style="display:none" role="dialog" aria-labelledby="delete-form-title" aria-describedby="delete-form-description">
<h2 id="delete-form-title">Delete download link from database</h2>
<form method="post" action="addon.php?action=delete&id=<?php echo $addonid; ?>" role="form">
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>"/>
<p id="delete-form-description">Are you sure you want to remove this download link from the database? This operation cannot be undone.</p>
<input type="hidden" id="deletelink" name="file"/>
<p><label for="log2">Log message</label>
<textarea id="log2" name="log" title="Optional log message displayed for this operation"></textarea></p>
<p><input type="submit" id="confirmDelete" value="Delete link permanently"/>
<button type="button" onclick="cancelRemove();">Cancel</button></p>
</form>
</div>
<script>
let buttons=document.getElementsByTagName("button");
for (let i=0; i<buttons.length; i++){
	if (buttons[i].textContent=="Edit"){
		buttons[i].addEventListener("click", editLink);
	}else if (buttons[i].textContent=="Remove"){
		buttons[i].addEventListener("click", removeLink);
	}
}
var file=document.getElementById("file");
var version=document.getElementById("version");
var channel=document.getElementById("channel");
var minimum=document.getElementById("minimum");
var lasttested=document.getElementById("lasttested");
var link=document.getElementById("link");
var editform=document.getElementById("edit-form");
var deleteform=document.getElementById("delete-form");
var deletelink=document.getElementById("deletelink");
var focusElement=null;
function editLink(e){
	focusElement=e.target;
	let row=this.parentNode.parentNode;
	file.value=row.childNodes[1].textContent;
	file.readOnly=true;
	version.value=row.childNodes[3].textContent;
	channel.value=row.childNodes[5].textContent;
	minimum.value=row.childNodes[7].textContent;
	lasttested.value=row.childNodes[9].textContent;
	link.value=row.childNodes[11].textContent;
	editform.style.display="block";
	file.focus();
}
function removeLink(e){
	focusElement=e.target;
	deletelink.value=this.parentNode.parentNode.id;
	deleteform.style.display="block";
	document.getElementById("confirmDelete").focus();
}
function newlink(e){
	focusElement=e.target;
	editform.style.display="block";
	file.focus();
}
document.getElementById("newlink").addEventListener("click", newlink);
function cancelEdit(e){
	focusElement.focus();
	editform.style.display="none";
	focusElement=null;
	file.value="";
	file.readOnly=false;
	version.value="";
	channel.value="";
	minimum.value="";
	lasttested.value="";
	link.value="";
}
function cancelRemove(e){
	focusElement.focus();
	deleteform.style.display="none";
	focusElement=null;
	deletelink.value="";
}
</script>
<?php
			include ("footer.php");
		}
	}else{
		logMessage($db, "Attempt to work on an add-on which does not exist");
		die("The specified add-on is not registered in the database");
	}
	$addonexists->finalize();
	$db->close();
}else{
	header("location: index.php");
}
?>