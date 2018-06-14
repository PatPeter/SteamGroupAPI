<?php
require_once('Autoloader.php');

use libsteam\group\history\Feed;
use libsteam\common\RSA;

if (isset($_POST['table']) && isset($_POST['group'])) {
	$feed = new feed($_POST['table'],$_POST['group']);
	$feed->database();
	echo "Did it!";
	$feed->input_current();
	exit();
} else {
	$feed = new feed(null,null);
	echo "[libsteam] Creating default feed class.<br />\n";
}

if (isset($_POST['login'])) {
	setcookie('feed_username',$_POST['username'],time()+86400,"/",$_SERVER['SERVER_NAME'],false,true);
	setcookie('feed_password',md5($_POST['password']),time()+86400,"/",$_SERVER['SERVER_NAME'],false,true);
	header("Location: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
	exit();
}
?>
<!DOCTYPE html PUBLIC
	"-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
	<title>libsteam Server Administration</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="style.css" type="text/css" />
	<script type="text/javascript" src="js/rsa.js"></script>
	<script type="text/javascript" src="js/jsbn.js"></script>
</head>
	<body>
		<script type="text/javascript">
		var pubKey = RSA.getPublicKey( "AB41C09B14B6610914D68BF83EFDDC3A0771305EAB8D5AABEB497B87DC918C13C0BA621977EEFFB90121288DA265077CF4416AA6EA34B2B219FFE112DAF2D0CAC9F1E9D70E44F411832B16CED5F34BD3680A1A727EDC93C269ABC01BC863377EEFC6528BC29BF186D66A8AFB93DE861FCF563C2C0A5EBE3E27D32E6362200BF0FDD331E8A34E870FB66A553B28418B4C76117BCC09A2A3DF70B03633D612DDE93B32CDE847D7C02BA21E1B8FB52AD2FCD0A621AD1E26C57648B08F7C17A2511ED658803120254AD85317AC648A63559D2592E65483A382401C3531A4C3DEB4C2DA333788A2A0F8A73A55E3D8AB6C62796289C594234424619E7A0C0D18EADD13", "010001" );
		document.write(JSON.stringify(pubKey));
		document.write("<br /><br />");
		var pkcs1pad2 = RSA.pkcs1pad2("password1", (pubKey.modulus.bitLength()+7)>>3);
		document.write(pkcs1pad2.toString());
		document.write("<br /><br />");
		var encryptedPassword = RSA.encrypt( "password1", pubKey );
		document.write(encryptedPassword);
		document.write("<br /><br />");
		</script>
		<?php
			/*$pubKey = RSA::getPublicKey("AB41C09B14B6610914D68BF83EFDDC3A0771305EAB8D5AABEB497B87DC918C13C0BA621977EEFFB90121288DA265077CF4416AA6EA34B2B219FFE112DAF2D0CAC9F1E9D70E44F411832B16CED5F34BD3680A1A727EDC93C269ABC01BC863377EEFC6528BC29BF186D66A8AFB93DE861FCF563C2C0A5EBE3E27D32E6362200BF0FDD331E8A34E870FB66A553B28418B4C76117BCC09A2A3DF70B03633D612DDE93B32CDE847D7C02BA21E1B8FB52AD2FCD0A621AD1E26C57648B08F7C17A2511ED658803120254AD85317AC648A63559D2592E65483A382401C3531A4C3DEB4C2DA333788A2A0F8A73A55E3D8AB6C62796289C594234424619E7A0C0D18EADD13", "010001");
			print_r($pubKey);
			$encryptedPassword = RSA::encrypt("password1", $pubKey);
			echo $encryptedPassword;*/
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
		<h2>RSS Feed Login</h2>
		<form name="login" action="admin.php" method="post">
			<input type="text"     name="username" id="username" />
			<input type="password" name="password" id="password" />
			<input type="submit"   name="login"    id="login"    value="Submit" />
		</form>
	</body>
</html>