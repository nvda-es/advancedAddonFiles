<?php
function set_title($title){
	global $db_file;
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title><?php echo $title; ?> | NVDA add-on files manager</title>
</head>
<body>
<p><a href="#main">Skip to main content</a></p>
<?php
if (isset($_SESSION['username'])){
?>
<nav role="navigation">
<h2>Navigation menu</h2>
<ul>
<li><a href="index.php">Home</a></li>
<?php
if ($_SESSION['role']=='2'){
?>
<li><a href="users.php">Users</a></li>
<?php
}
?>
<li><a href="addons.php">Add-ons</a></li>
<li><a href="log.php">Activity log</a></li>
</ul>
</nav>
<section role="region" aria-label="User information">
<h2>User information</h2>
<?php
echo "<p>You are signed in as ".$_SESSION['fullname']."</p>";
switch (intval($_SESSION['role'])){
	case 0:
		echo "<p>You are an add-on author. You can modify and update your own add-ons.</p>";
		break;
	case 1:
		echo "<p>You are an add-ons reviewer. You can add, modify and remove all registered add-ons on the system.</p>";
		break;
	case 2:
		echo "<p>You are an administrator. You can add, modify and remove all registered add-ons, and manage user accounts.</p>";
		break;
}
?>
<p><a href="profile.php">Update your profile</a> | <a href="logout.php">Logout</a></p>
</section>
<?php
}else{
?>
<nav role="navigation">
<h2>Navigation menu</h2>
<ul>
<li><a href="index.php">Login</a></li>
<li><a href="log.php">Activity log</a></li>
</ul>
</nav>
<?php
}
?>
<main id="main" role="main">
<header role="banner">
<h1><?php echo $title; ?></h1>
</header>
<?php
}
?>