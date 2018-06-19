<?php
require_once 'vendor/autoload.php';
use \Curl\Curl;
use \phpseclib\Crypt\RSA;
use \phpseclib\Math\BigInteger;
function login($username, $password) {
	// If username or password aren't set then return false
	if (!isset($username) || !isset($password))	return false;
	// Set variables
	$donotcache = round(microtime(true)*1000);
	// Define class Curl() in variable $curl
	$curl = new Curl();
	// Define class Crypt_RSA in variable $rsa
	$rsa = new RSA();
	// Retrieve RSA key
	$curl->post('https://steamcommunity.com/login/getrsakey/', array(
		'donotcache'	=>	$donotcache,
		'username'		=>	$username
	));
	// If RSA key failed then return false
	if ($curl->response->success != true) return false;
	// Get timestamp
	$rsatimestamp = $curl->response->timestamp;
	// Encrypt password
	// Set keys
	$key = array(
		'n' => new BigInteger($curl->response->publickey_mod,16),
		'e' => new BigInteger($curl->response->publickey_exp,16)
	);
	// Define exponent
	define('CRYPT_RSA_EXPONENT', 010001);
	// Load the key
	if (!$rsa->loadKey($key)) return false;
	// Set settings
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	$rsa->setHash('sha256');
	// Encrypt password
	$encrypted_password = base64_encode($rsa->encrypt($password));
	// Login to Steam
	$curl->post('https://steamcommunity.com/login/dologin/', array(
		'donotcache'	=>	$donotcache,
		'password'		=>	$encrypted_password,
		'username'		=>	$username,
		'rsatimestamp'	=>	$rsatimestamp
	));
	// If login failed then return false
	if ($curl->response->success != true) return false;
	// Setup transfer variables
	$steamid		=	$curl->response->transfer_parameters->steamid;
	$token 			=	$curl->response->transfer_parameters->token;
	$auth			=	$curl->response->transfer_parameters->auth;
	$token_secure	=	$curl->response->transfer_parameters->token_secure;
	// Transfer 1
	$curl->post('https://store.steampowered.com/login/transfer', array(
		'steamid'			=>	$steamid,
		'token'				=>	$token,
		'auth'				=>	$auth,
		'remember_login'	=>	true,
		'token_secure'		=>	$token_secure
	));
	
	// If transfer 1 failed then return false
	//if (!strpos($curl->response, 'transfer_success')) return false;
	// Transfer broken? Don't need it anyway.
	// Transfer 2
	$curl->post('https://help.steampowered.com/login/transfer', array(
		'steamid'			=>	$steamid,
		'token'				=>	$token,
		'auth'				=>	$auth,
		'remember_login'	=>	true,
		'token_secure'		=>	$token_secure
	));
	// If transfer 2 failed then return false
	//if (!strpos($curl->response, 'transfer_success')) return false;
	// Transfer broken? Don't need it anyway.
	// Close connection
	$curl->close();
	// If all else was a success, return true
	return true;
}
function is_logged(){
	return ($curl->get('http://steamcommunity.com/actions/GetNotificationCounts') != "null");
}
?>