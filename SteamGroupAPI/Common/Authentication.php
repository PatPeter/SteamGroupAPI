<?php
namespace SteamGroupAPI\Common;

use \Curl\Curl;
use \phpseclib\Crypt\RSA;
use \phpseclib\Math\BigInteger;

class Authentication {
	public static function login($username, $password) {
		// If username or password aren't set then return false
		if (!isset($username) || !isset($password))	return false;
		// Set variables
		$donotcache = round(microtime(true)*1000);
		// Define class Curl() in variable $curl
		$curl = new Curl();
		$curl->xmlDecoder = function($response)
		{
			$xml_obj = @simplexml_load_string($response, null, LIBXML_NOCDATA);
			if (!($xml_obj === false)) {
				$response = $xml_obj;
			}
			return $response;
		};
		$curl->setCookieJar(sys_get_temp_dir() . '/cookies.txt');
		$curl->setCookieFile(sys_get_temp_dir() . '/cookies.txt');
		file_put_contents(sys_get_temp_dir() . '/cookies.txt', 'steamcommunity.com	FALSE	/	FALSE	0	timezoneOffset	-18000,0', FILE_APPEND | LOCK_EX, null);
		// -18000 GMT-06:00
		//$curl->setCookieString('steamcommunity.com	FALSE	/	FALSE	0	timezoneOffset	-18000,0');
		//if (Authentication::is_logged($curl)) {
			//return $curl;
		//}
		
		// Define class Crypt_RSA in variable $rsa
		$rsa = new \Crypt_RSA();
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
			'n' => new \Math_BigInteger($curl->response->publickey_mod,16),
			'e' => new \Math_BigInteger($curl->response->publickey_exp,16),
		);
		// Define exponent
		define('CRYPT_RSA_EXPONENT', 010001);
		// Load the key
		if (!$rsa->loadKey($key)) {
			error_log('Could not load RSA key.');
			return false;
		}
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
		var_dump($curl->response);
		// If login failed then return false
		if ($curl->response->success != true) {
			error_log('Do Login was not successful.');
			print_r($curl->response);
			return false;
		}
		$curl->setCookie('timezoneOffset', '-18000,0');
		$curl->setCookie('timezoneOffset-steamcommunity.com', '-18000,0');
		
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
		//$curl->close();
		// If all else was a success, return true
		return $curl;
	}
	public static function is_logged($curl){
		print_r($curl->get('http://steamcommunity.com/actions/GetNotificationCounts'));
		return ($curl->get('http://steamcommunity.com/actions/GetNotificationCounts') != "null");
	}
}
?>