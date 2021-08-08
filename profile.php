<?php
include ("config.php");
session_name($session_name);
session_start();
if (isset($_SESSION['username'])){
	$db=new SQLite3($db_file);
	if (isset($_POST['update'])){
		if (!isset($_POST['token']) || $_POST['token'] != $_SESSION['token']){
			die("Invalid CSRF token provided.");
		}
		$fullname=SQLite3::escapeString($_POST['fullname']);
		$email=SQLite3::escapeString($_POST['email']);
		$password=SQLite3::escapeString($_POST['password']);
		$query="update users set fullname='".$fullname."', email='".$email."'";
		if ($password!=""){
			$password=password_hash($password, PASSWORD_DEFAULT);
			$query=$query.", password='".$password."'";
		}
		$query=$query." where id=".$_SESSION['id'];
		$db->exec($query);
		header("Location: index.php");
	}else{
		include ("header.php");
		$result=$db->query("select username, fullname, email from users where id=".$_SESSION['id']);
		$row=$result->fetchArray(SQLITE3_NUM);
		$username=$row[0];
		$fullname=$row[1];
		$email=$row[2];
		$result->finalize();
		set_title("My profile");
?>
<p>On this page, you can view and update your account details. Please, leave password field empty if you don't want to change it. Your user name is <?php echo $username; ?>, and can't be changed. If you want to change your user name, contact an administrator.</p>
<form method="post" action="profile.php" role="form">
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>"/>
<label for="fullname">Full name*</label>
<input type="text" id="fullname" name="fullname" required="" aria-required="true" value="<?php echo $fullname; ?>"/>
<label for="email">E-mail address*</label>
<input type="email" id="email" name="email" required="" aria-required="true" value="<?php echo $email; ?>"/>
<label for="password">Change password</label>
<input type="password" id="password" name="password"/>
<input type="submit" name="update" value="Update account details"/>
</form>
<?php
		include("footer.php");
	}
	$db->close();
}else{
	header("location: index.php");
}
?>