<?php
include ("config.php");
session_name($session_name);
session_start();
if (isset($_SESSION['username'])){
	if ($_SESSION['role']!="2"){
		logMessage($db, "Attempt to manage user accounts without the required privileges");
		header("Location: index.php");
	}
	$db=new SQLite3($db_file);
	if (isset($_GET['action'])){
		if ($_GET['action']=="edit"){
			$username=SQLite3::escapeString($_POST['username']);
			$fullname=SQLite3::escapeString($_POST['fullname']);
			$email=SQLite3::escapeString($_POST['email']);
			if ($_POST['password']!=""){
				$password=password_hash(SQLite3::escapeString($_POST['password']), PASSWORD_DEFAULT);
			}else{
				$password="";
			}
			$role=SQLite3::escapeString($_POST['role']);
			$userid=SQLite3::escapeString($_POST['userid']);
			if ($userid==""){
				$userid=$db->query("select count(*) from users")->fetchArray()[0];
				$db->exec("insert into users (id, username, fullname, email, password, role) values (".$userid.", '".$username."', '".$fullname."', '".$email."', '".$password."', ".$role.")");
				logMessage($db, "Created new user account: ".$username.". ".$_POST['log']);
			}else{
				$query="update users set username='".$username."', fullname='".$fullname."', email='".$email."', ";
				if ($password!=""){
					$query=$query."password='".$password."', ";
				}
				$query=$query."role=".$role." where id=".$userid;
				$db->exec($query);
				logMessage($db, "Updated user account: ".$username.". ".$_POST['log']);
			}
		}elseif ($_GET['action']=="delete"){
			if ($_POST['deleteuser']!=$_SESSION['id']){
				$db->exec("delete from users where id=".SQLite3::escapeString($_POST['deleteuser']));
				$db->exec("update users set id=id-1 where id>".SQLite3::escapeString($_POST['deleteuser']));
				logMessage($db, "Deleted user account: ".$_POST['deleteuser'].". ".$_POST['log']);
			}
		}
		header("location: users.php");
	}else{
		include ("header.php");
		set_title("Manage users");
?>
<h2>List of current registered users</h2>
<button id="adduser">Add new user</button>
<table>
<caption>List of current registered users</caption>
<thead>
<tr>
<th>ID</th>
<th>Username</th>
<th>Full name</th>
<th>E-mail address</th>
<th>Password hash</th>
<th>Role (0=author, 1=reviewer, 2=admin)</th>
<th>Edit</th>
<th>Delete</th>
</tr>
</thead>
<tbody>
<?php
$result=$db->query("select * from users");
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
<div id="edit-form" style="display:none">
<h2>Create or update user</h2>
<form method="post" action="users.php?action=edit" role="form">
<input type="hidden" name="userid" id="userid"/>
<label for="username">User name*</label>
<input type="text" name="username" id="username" required="" aria-required="true"/>
<label for="fullname">Full name*</label>
<input type="text" name="fullname" id="fullname" aria-required="true" required=""/>
<label for="email">E-mail address*</label>
<input type="email" name="email" id="email" required="" aria-required="true"/>
<label for="password">Password*</label>
<input type="text" name="password" id="password" required="" aria-required="true" title="Provide a password you don't use anywhere else. Leave empty if you are updating an user account and don't want to change the password"/>
<label for="role">User role*</label>
<select id="role" name="role" required="" aria-required="true">
<option id="optionAuthor" value="0">Author</option>
<option id="optionReviewer" value="1">Reviewer</option>
<option id="optionAdmin" value="2">Administrator</option>
</select>
<label for="log">Log message</label>
<textarea id="log" name="log" title="Optional log message displayed for this operation"></textarea>
<input type="submit" value="Submit"/>
<button type="button" onclick="cancelEdit();">Cancel</button>
</form>
</div>
<div id="delete-form" style="display:none">
<h2>Delete user from database</h2>
<form method="post" action="users.php?action=delete" role="form">
<p>Are you sure you want to remove this user from the database? This operation cannot be undone.</p>
<input type="hidden" id="deleteuser" name="deleteuser"/>
<label for="log2">Log message</label>
<textarea id="log2" name="log" title="Optional log message displayed for this operation"></textarea>
<input type="submit" id="confirmDelete" value="Delete user permanently"/>
<button type="button" onclick="cancelRemove();">Cancel</button>
</form>
</div>
<script>
let buttons=document.getElementsByTagName("button");
for (let i=0; i<buttons.length; i++){
	if (buttons[i].textContent=="Edit"){
		buttons[i].addEventListener("click", editUser);
	}else if (buttons[i].textContent=="Remove"){
		buttons[i].addEventListener("click", removeUser);
	}
}
var userid=document.getElementById("userid");
var username=document.getElementById("username");
var fullname=document.getElementById("fullname");
var email=document.getElementById("email");
var password=document.getElementById("password");
var role=document.getElementById("role");
var optionAuthor=document.getElementById("optionAuthor");
var optionReviewer=document.getElementById("optionReviewer");
var optionAdmin=document.getElementById("optionAdmin");
var editform=document.getElementById("edit-form");
var deleteform=document.getElementById("delete-form");
var deleteuser=document.getElementById("deleteuser");
var focusElement=null;
function editUser(e){
	focusElement=e.target;
	let row=this.parentNode.parentNode;
	userid.value=row.childNodes[1].textContent;
	username.value=row.childNodes[3].textContent;
	fullname.value=row.childNodes[5].textContent;
	email.value=row.childNodes[7].textContent;
	password.removeAttribute("aria-required");
	password.removeAttribute("required");
	switch(row.childNodes[11].textContent){
		case "0":
			optionAuthor.selected="selected";
			break;
		case "1":
			optionReviewer.selected="selected";
			break;
		case "2":
			optionAdmin.selected="selected";
			break;
	}
	editform.style.display="block";
	username.focus();
}
function removeUser(e){
	focusElement=e.target;
	deleteuser.value=this.parentNode.parentNode.id;
	deleteform.style.display="block";
	document.getElementById("confirmDelete").focus();
}
function adduser(e){
	focusElement=e.target;
	editform.style.display="block";
	username.focus();
	username.parentNode.reset();
	userid.value="";
	password.required="";
	password.setAttribute("aria-required", true);
}
document.getElementById("adduser").addEventListener("click", adduser);
function cancelEdit(e){
	focusElement.focus();
	editform.style.display="none";
	focusElement=null;
}
function cancelRemove(e){
	focusElement.focus();
	deleteform.style.display="none";
	focusElement=null;
}
</script>
<?php
		include ("footer.php");
	}
	$db->close();
}else{
	header("location: index.php");
}
?>