<?php
include ("config.php");
include ("header.php");
set_title("Installation");
if (file_exists($db_file)){
	echo "<p>The database has been already created. If you want to reinstall this application, remove the database file first.</p>";
}else{
	$db=new SQLite3($db_file);
	$db->exec("create table addons (id integer primary key, author text, name text, summary text, description text, url text)");
	$db->exec("create table links (id integer, file text unique, version text, channel text, minimum text, lasttested text, link text, downloads integer)");
	$db->exec("create table users (id integer primary key, username text unique, fullname text, email text, password text, role integer)");
	$db->exec("create table permissions (user integer, addon integer)");
	$db->exec("insert into users (id, username, fullname, email, password, role) values (0, 'admin', 'Main administrator', 'admin@localhost', '".password_hash("admin", PASSWORD_DEFAULT)."', 2)");
	$db->exec("create table log (id integer primary key, date text, user text, message text)");
	echo "<p>Database created successfully. You can now access the system using 'admin' as your user name and 'admin' as password. Please, change this information as soon as you login.</p>";
	echo '<p><a href="index.php">Click here to login</a></p>';
	$db->close();
}
include ("footer.php");
?>