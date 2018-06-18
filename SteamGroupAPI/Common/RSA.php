<?php
namespace libsteam\common;

/*
 * Created on 2014/03/01 at 02:44:48 PM
 */
require('Math/BigInteger.php');

class RSAPublicKey {
	public $modulus;
	public $encryptionExponent;
	
	public function __construct($modulus_hex, $encryptionExponent_hex) {
		$this->modulus = new \Math_BigInteger($modulus_hex, 16);
		$this->encryptionExponent = new \Math_BigInteger($encryptionExponent_hex, 16);
	}
}

class Base64 {
	const BASE64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	
	public static function encode($input) {
		if (!isset($input)) {
			return false;
		}
		$output = '';
		$i = 0;
		do {
			$chr1 = ord($input[$i++]);
			$chr2 = ord($input[$i++]);
			$chr3 = ord($input[$i++]);
			$enc1 = $chr1 >> 2;
			$enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
			$enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
			$enc4 = $chr3 & 63;
			if (is_nan($chr2)) {
				$enc3 = $enc4 = 64;
			} else if (is_nan($chr3)) {
				$enc4 = 64;
			}
			$output .= substr(self::BASE64, $enc1, 1) . 
				substr(self::BASE64, $enc2, 1) . 
				substr(self::BASE64, $enc3, 1) . 
				substr(self::BASE64, $enc4, 1);
		} while ($i < strlen($input));
		return $output;
	}
	
	public static function decode($input) {
		if (!isset($input)) {
			return false;
		}
		$output = '';
		$i = 0;
		do {
			$enc1 = strpos($input[$i++]);
			$enc2 = strpos($input[$i++]);
			$enc3 = strpos($input[$i++]);
			$enc4 = strpos($input[$i++]);
			$output = ord(($enc1 << 2) | ($enc2 >> 4));
			if ($enc3 != 64) {
				$output .= ord((($enc2 & 15) << 4) | ($enc3 >> 2));
			}
			if ($enc4 != 64) {
				$output .= ord((($enc3 & 3) << 6) | $enc4);
			}
		} while ($i < strlen($input));
		return $output;
	}
}

class Hex {
	const HEX = "0123456789abcdef";
	
	public static function encode($input) {
		if (!isset($input)) {
			return false;
		}
		$output = '';
		$i = 0;
		do {
			$k = ord($input[$i++]);
			$output .= substr(self::HEX, ($k >> 4) & 0xf, 1) + substr(HEX, $k & 0xf, 1);
		} while ($i < strlen($input));
		return $output;
	}
	
	public static function decode($input) {
		if (!isset($input)) {
			return false;
		}
		echo "Input: " . $input . "<br />";
		
		$input = preg_replace('/[^0-9abcdef]/', '', $input);
		
		echo "Hex input: " . $input . "<br />";
		
		$output = '';
		$i = 0;
		do {
			echo "i: " . $i . " < " . $input . "<br />";
			$output .= ord(
				((strpos(self::HEX, $input[$i++]) << 4) & 0xf0) | 
				(strpos(self::HEX, $input[$i++]) & 0xf0)
			);
		} while ($i < strlen($input));
		return $output;
	}
}

class RSA {
	public static function getPublicKey($modulus_hex, $exponent_hex) {
		return new RSAPublicKey($modulus_hex, $exponent_hex);
	}
	
	public static function encrypt($data, $pubkey) {
		if (!isset($pubkey)) {
			return false;
		}
		/* @var $data BigInteger */
		$data = self::pkcs1pad2($data, (strlen($pubkey->modulus->toBits()) + 7) >> 3);
		if (!isset($data)) {
			return false;
		}
		$data = $data->modPow($pubkey->encryptionExponent, $pubkey->modulus);
		if (!isset($data)) {
			return false;
		}
		$data = $data->toString(16);
		return Base64::encode(Hex::decode($data));
	}
	
	public static function pkcs1pad2($data, $keysize) {
		if ($keysize < strlen($data) + 11) {
			return null;
		}
		$buffer = array();
		$i = strlen($data) - 1;
		while ($i >= 0 && $keysize > 0) {
			$buffer[--$keysize] = ord($data[$i--]);
		}
		$buffer[--$keysize] = 0;
		while ($keysize > 2) {
			$buffer[--$keysize] = floor((rand(0, 99) / 100) * 254) + 1;
		}
		$buffer[--$keysize] = 2;
		$buffer[--$keysize] = 0;
		return new \Math_BigInteger(implode($buffer));
	}
}