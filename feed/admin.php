<?php
require('../cfeed.php');

if (isset($_POST['table']) && isset($_POST['group'])) {
	$feed = new feed($_POST['table'],$_POST['group']);
	$feed->database();
	echo "Did it!";
	$feed->input_current();
	exit();
} else {
	$feed = new feed(null,null);
	echo "Creating default feed class.<br />\n";
}
$feed->check_cookies(array('feed_username'),array('feed_password'));
if (isset($_POST['login'])) {
	setcookie('feed_username',$_POST['username'],time()+86400,"/",$_SERVER['SERVER_NAME'],false,true);
	setcookie('feed_password',md5($_POST['password']),time()+86400,"/",$_SERVER['SERVER_NAME'],false,true);
	header("Location: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	exit();
}

//
//
//$feed->main(15);
?>
<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<title>Steam RSS Feed Administration</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="style.css" type="text/css" />
</head>
	<body>
		<?php
		if ($feed->check_password($_COOKIE['feed_username'],$_COOKIE['feed_password']) == "GRANTED_ACCESS") {
		?>
		<h2>Create Table</h2>
		<form name="create_table" action="admin.php" method="post">
		<table><tr><td>Table:
		</td><td><input type="text" name="table" id="table" maxLength="16" />
		</td></tr><tr><td>Steam Group URL ID:
		</td><td><input type="text" name="group" id="" maxLength="32" />
		</td></tr><tr><td colspan="2"><input type="submit" name="create_table" id="create_table" value="Create Table" />
		</td></tr></table>
		</form>
		<div align="center">
		<form name="logout" action="admin.php" method="post">
			<input type="submit" name="logout" id="logout" value="Logout" />
		</form>
		</div>
		<?php
		} else {
		?>
		<h2>RSS Feed Login</h2>
		<form name="login" action="admin.php" method="post">
			<input type="text"     name="username" id="username" />
			<input type="password" name="password" id="password" />
			<input type="submit"   name="login"    id="login"    value="Submit" />
		</form>
		<?php
		}
		?>
	</body>
<?php unset($feed); ?>
</html>