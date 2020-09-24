<?php
include ("config.php");
if (!file_exists($db_file)){
	header("Location: install.php");
}
include ("header.php");
session_name($session_name);
session_start();
if (isset($_SESSION['username'])){
	set_title("Home");
	$db=new SQLite3($db_file);
	$totalAddons=$db->query("select count(*) from addons")->fetchArray(SQLITE3_NUM)[0];
	$totalLinks=$db->query("select count(*) from links")->fetchArray(SQLITE3_NUM)[0];
	$db->close();
?>
<p>Welcome to the NVDA add-on files management application. Use the links provided above to manage your add-ons. In case you are a reviewer or an administrator, you may see additional options. Currently, there are <?php echo $totalAddons; ?> add-ons registered in the database, with <?php echo $totalLinks; ?> links.</p>
<?php
}else{
	set_title("Login");
	if (isset($_GET['error'])){
		echo "<p>The username or password you have entered are wrong. Please, try again.</p>";
	}
?>
<form role="form" aria-label="Login form" method="post" action="login.php">
<p>Fill in the following fields in order to sign in.</p>
<p><label for="username">Username (required)</label>
<input id="username" name="username" type="text" required="" aria-required="true" title="Username" <?php
if (isset($_GET['user'])){
echo 'value="'.$_GET['user'].'"';
}
?>/></p>
<p><label for="password">Password (required)</label>
<input id="password" name="password" type="password" required="" aria-required="true" title="Password"/></p>
<p><input type="submit" value="Sign in"/></p>
</form>
<?php
}
include ("footer.php");
?>