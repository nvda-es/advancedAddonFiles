<?php
include ("config.php");
session_name($session_name);
session_start();
if (isset($_SESSION['username'])){
	$db=new SQLite3($db_file);
	if (isset($_GET['action'])){
		if ($_SESSION['role']=="0"){
			$result=$db->query("select * from permissions where user=".$_SESSION['id']." and addon=".SQLite3::escapeString($_POST['addonid']));
			if (!$result->fetchArray(SQLITE3_NUM)){
				logMessage($db, "Attempt to access an add-on owned by another user without required privileges");
				die("Permission denied. You cannot perform actions on this add-on.");
			}
		}
		if ($_GET['action']=="edit"){
			$addonid=SQLite3::escapeString($_POST['addonid']);
			$addonauthor=SQLite3::escapeString($_POST['addonauthor']);
			$addonname=SQLite3::escapeString($_POST['addonname']);
			$addonsummary=SQLite3::escapeString($_POST['addonsummary']);
			$addondescription=SQLite3::escapeString($_POST['addondescription']);
			$addonurl=SQLite3::escapeString($_POST['addonurl']);
			$addonowner=SQLite3::escapeString($_POST['addonowner']);
			if ($addonid==""){
				if ($_SESSION['role']=="0"){
					logMessage($db, "Attempt to register an add-on without required privileges.");
					die("Permission denied. You don't have enough privileges to register new add-ons");
				}
				$addonid=$db->query("select count(*) from addons")->fetchArray()[0];
				$db->exec("insert into addons (id, author, name, summary, description, url) values (".$addonid.", '".$addonauthor."', '".$addonname."', '".$addonsummary."', '".$addondescription."', '".$addonurl."')");
				$db->exec("insert into permissions (user, addon) values (".$addonowner.", ".$addonid.")");
				logMessage($db, "Registered new add-on: ".$addonname.". ".$_POST['log']);
			}else{
				$db->exec("update addons set name='".$addonname."', author='".$addonauthor."', summary='".$addonsummary."', description='".$addondescription."', url='".$addonurl."' where id=".$addonid);
				$db->exec("update permissions set user=".$addonowner." where addon=".$addonid);
				logMessage($db, "Updated add-on: ".$addonname.". ".$_POST['log']);
			}
		}elseif ($_GET['action']=="delete"){
			if ($_SESSION['role']=="0"){
				logMessage($db, "Attempt to delete an add-on without required privileges.");
				die("Permission denied. You don't have enough privileges to remove new add-ons");
			}
			$addonid=SQLite3::escapeString($_POST['deleteaddon']);
			$db->exec("delete from addons where id=".$addonid);
			$db->exec("delete from links where id=".$addonid);
			$db->exec("delete from permissions where addon=".$addonid);
			$db->exec("update addons set id=id-1 where id>".$addonid);
			$db->exec("update links set id=id-1 where id>".$addonid);
			$db->exec("update permissions set addon=addon-1 where addon>".$addonid);
			logMessage($db, "Deleted add-on with id: ".$addonid.". ".$_POST['log']);
		}else{
			logMessage($db, "Attempt to perform an unrecognized action on the system");
			die("Unrecognized action. Please, don't try to change manually the URLs from your web browser address bar. Use the interface provided by this application instead.");
		}
		header("location: addons.php");
	}else{
		include ("header.php");
		set_title("Manage addons");
?>
<p>On this page, you can manage the add-ons registered in the system. If you are an add-on author, you can edit your own add-ons, update versions and channels, and modify download links. If you are a reviewer or an administrator, you can perform all operations on all add-ons.</p>
<h2>List of registered add-ons</h2>
<?php
if ($_SESSION['role']!="0"){
?>
<button id="newaddon">Register new add-on</button>
<?php
}
?>
<table>
<caption>List of registered add-ons</caption>
<thead>
<tr>
<th>Id (click to manage links)</th>
<th>Author</th>
<th>Name</th>
<th>Summary</th>
<th>Description</th>
<th>URL</th>
<th>Edit</th>
<?php
if ($_SESSION['role']!="0"){
?>
<th>Remove</th>
<?php
}
?>
</tr>
</thead>
<tbody>
<?php
$query="select * from addons";
if ($_SESSION['role']=="0"){
	$query="select * from addons, permissions where addons.id=permissions.addon and permissions.user=".$_SESSION['id'];
}
$result=$db->query($query);
while ($row=$result->fetchArray(SQLITE3_NUM)){
	echo "<tr id='".$row[0]."'>\n";
	foreach ($row as $item){
		if ($item===$row[0]){
			echo "<th scope='row'><a href='addon.php?id=".$row[0]."'>".$item."</a></th>\n";
		}else{
			echo "<td>".$item."</td>\n";
		}
	}
	echo "<td><button>Edit</button></td>\n";
	if ($_SESSION['role']!="0"){
		echo "<td><button>Remove</button></td>\n";
	}
	echo "</tr>\n";
}
$result->finalize();
?>
</tbody>
</table>
<div role="dialog" id="edit-form" style="display:none">
<h2>Create or update add-on</h2>
<form method="post" action="addons.php?action=edit" role="form">
<input type="hidden" name="addonid" id="addonid"/>
<label for="addonauthor">Add-on author*</label>
<input type="text" id="addonauthor" name="addonauthor" required="" aria-required="true"/>
<label for="addonname">Add-on name*</label>
<input type="text" name="addonname" id="addonname" required="" aria-required="true"/>
<label for="addonsummary">Add-on summary*</label>
<input type="text" name="addonsummary" id="addonsummary" aria-required="true" required=""/>
<label for="addondescription">Add-on description*</label>
<textarea name="addondescription" id="addondescription" required="" aria-required="true"></textarea>
<label for="addonurl">Add-on URL*</label>
<input type="url" name="addonurl" id="addonurl" required="" aria-required="true"/>
<label for="addonowner">Add-on owner*</label>
<select id="addonowner" name="addonowner" required="" aria-required="true">
<?php
$result=$db->query("select id, fullname from users");
while ($row=$result->fetchArray(SQLITE3_NUM)){
	if ($row[0]==$_SESSION['id']){
		echo '<option selected="" value="'.$row[0].'">'.$row[1].'</option>';
	}else{
		echo '<option value="'.$row[0].'">'.$row[1].'</option>';
	}
}
$result->finalize();
?>
</select>
<label for="log">Log message</label>
<textarea id="log" name="log" title="Optional log message displayed for this operation"></textarea>
<input type="submit" value="Submit"/>
<button type="button" onclick="cancelEdit();">Cancel</button>
</form>
</div>
<?php
if ($_SESSION['role']!="0"){
?>
<div role="dialog" id="delete-form" style="display:none">
<h2>Delete add-on from database</h2>
<form method="post" action="addons.php?action=delete" role="form">
<p>Are you sure you want to remove this add-on from the database? All links, update channels and related information will be deleted too. This operation cannot be undone.</p>
<input type="hidden" id="deleteaddon" name="deleteaddon"/>
<label for="log2">Log message</label>
<textarea id="log2" name="log" title="Optional log message displayed for this operation"></textarea>
<input type="submit" id="confirmDelete" value="Delete add-on permanently"/>
<button type="button" onclick="cancelRemove();">Cancel</button>
</form>
</div>
<?php
}
?>
<script>
let buttons=document.getElementsByTagName("button");
for (let i=0; i<buttons.length; i++){
	if (buttons[i].textContent=="Edit"){
		buttons[i].addEventListener("click", editAddon);
	}else if (buttons[i].textContent=="Remove"){
		buttons[i].addEventListener("click", removeAddon);
	}
}
var addonid=document.getElementById("addonid");
var addonname=document.getElementById("addonname");
var addonsummary=document.getElementById("addonsummary");
var addondescription=document.getElementById("addondescription");
var addonurl=document.getElementById("addonurl");
var addonauthor=document.getElementById("addonauthor");
var editform=document.getElementById("edit-form");
var focusElement=null;
function editAddon(e){
	focusElement=e.target;
	let row=this.parentNode.parentNode;
	addonid.value=row.childNodes[1].textContent;
	addonauthor.value=row.childNodes[3].textContent;
	addonname.value=row.childNodes[5].textContent;
	addonsummary.value=row.childNodes[7].textContent;
	addondescription.value=row.childNodes[9].textContent;
	addonurl.value=row.childNodes[11].textContent;
	editform.style.display="block";
	addonauthor.focus();
}
<?php
if ($_SESSION['role']!="0"){
?>
var deleteform=document.getElementById("delete-form");
var deleteaddon=document.getElementById("deleteaddon");
function removeAddon(e){
	focusElement=e.target;
	deleteaddon.value=this.parentNode.parentNode.id;
	deleteform.style.display="block";
	document.getElementById("confirmDelete").focus();
}
function newaddon(e){
	focusElement=e.target;
	editform.style.display="block";
	addonauthor.focus();
}
document.getElementById("newaddon").addEventListener("click", newaddon);
function cancelRemove(e){
	focusElement.focus();
	deleteform.style.display="none";
	focusElement=null;
	deleteaddon.value="";
}
<?php
}
?>
function cancelEdit(e){
	focusElement.focus();
	editform.style.display="none";
	focusElement=null;
	addonid.value="";
	addonauthor.value="";
	addonname.value="";
	addonsummary.value="";
	addondescription.value="";
	addonurl.value="";
}
</script>
<?php
		$db->close();
		include ("footer.php");
	}
}else{
	header("location: index.php");
}
?>