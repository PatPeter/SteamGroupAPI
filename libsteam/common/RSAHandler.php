<?php
namespace libsteam\common;

use libsteam\common\RSAKeyGen;

// Stevish RSA version 2.1

// Copyright 2009, Stevish.com (with mad props to te developers of the PEAR RSA extension)
// This script is distributed under the terms of the GNU General Public License (GPL)
// See http://www.gnu.org/licenses/gpl.txt for license details

// Please use, distribute, modify, rip-off, sell or destroy this script however you see fit
// I only ask that you remove my copyright if you modify and re-release this.
// To make sure you have the genuine, up-to-date version, visit http://stevish.com/rsa-encryption-in-pure-php

// To use:
//
// $text = "Peter Piper picked a peck of pickled peppers";
// $RSA = new RSA_Handler();
// $keys = $RSA->generate_keypair(1024);
// $encrypted = $RSA->encrypt($text, $keys[0]);
// $decrypted = $RSA->decrypt($encrypted, $keys[1]);
// echo $decrypted; //Will print Peter Piper picked a peck of pickled peppers

class RSAHandler {
	function encrypt($text, $key) {
		list($p, $r, $keysize) = unserialize(base64_decode($key));
		$in = $this->blockify($text, $keysize);
		$out = '';
		foreach($in as $block) {
			if($block) {
				$cryptblock = $this->crypt_num($this->txt2num($block), $p, $r);
				$out .= $this->long_base_convert($cryptblock, 10, 145) . " ";
			}
		}
		return $out;
	}
	
	function decrypt($code, $key) {
		list($q, $r) = unserialize(base64_decode($key));
		$in = explode(" ", $code);
		$out = '';
		foreach($in as $block) {
			if($block) {
				$block = $this->long_base_convert($block, 145, 10);
				$out .= $this->num2txt($this->crypt_num($block, $q, $r));
			}
		}
		return $out;
	}
		
	function generate_keypair($bits = 1024) {
		$km = new RSAKeyGen();
		$keys = $km->make_keys($bits);
		//The keys are separated into arrays and then serialized and encoded in base64
		//This makes it easier to store and transmit them
		//
		//The private key should probably be encrypted with a user-supplied key (in AES or DES3)...
		//This way it can be stored on the server, yet still be secure. The user-supplied key should not be stored.
		$pub = base64_encode(serialize(array($keys[0], $keys[2], $bits)));
		$priv = base64_encode(serialize(array($keys[1], $keys[2], $bits)));
		return array($pub, $priv);
	}
	
	function crypt_num($num, $key, $mod) {
		//The powerhorse function. This is where the encryption/decryption actually happens.
		//This function is used whether you are encrypting or decrypting.
		return $this->powmod($num, $key, $mod);
	}

	function long_base_convert($numstring, $frombase, $tobase) {
		//Converts a long integer (passed as a string) to/from any base from 2 to 145
		$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-+=!@#$%^*(){[}]|:,.?/`~•¤¶§ÇüéâäàåçêëèïîìÄÅÉæÆôöòûùÿÖÜ¢£¥ƒáíóúñÑªº¿¬½¼¡«»¯ßµ±÷;<>";
		$fromstring = substr($chars, 0, $frombase);
		$tostring = substr($chars, 0, $tobase);

		$length = strlen($numstring);
		$result = '';
		for ($i = 0; $i < $length; $i++) {
			$number[$i] = strpos($fromstring, $numstring{$i});
		}
		do {
			$divide = 0;
			$newlen = 0;
			for ($i = 0; $i < $length; $i++) {
				$divide = $divide * $frombase + $number[$i];
				if ($divide >= $tobase) {
					$number[$newlen++] = (int)($divide / $tobase);
					$divide = $divide % $tobase;
				} elseif ($newlen > 0) {
					$number[$newlen++] = 0;
				}
			}
			$length = $newlen;
			$result = $tostring{$divide} . $result;
		} while ($newlen != 0);
		return $result;
	}
	
	function blockify($in, $keysize) {
		//Calculate blocksize by keysize
		$b_len = floor($keysize/8);
		return str_split($in, $b_len);
	}
	
	function txt2num($str) {
		//Turns regular text into a number that can be manipulated by the RSA algorithm
		$result = '0';
		$n = strlen($str);
		do {
			$result = bcadd(bcmul($result, '256'), ord($str{--$n}));
		} while ($n > 0);
		return $result;
	}
	
	function num2txt($num) {
		//Turns the numeric representation of text (as output by txt2num) back into text
		$result = '';
		do {
			$result .= chr(bcmod($num, '256'));
			$num = bcdiv($num, '256');
		} while (bccomp($num, '0'));
		return $result;
	}
	
	function powmod($num, $pow, $mod) {
		if (function_exists('bcpowmod')) {
			// bcpowmod is only available under PHP5
			return bcpowmod($num, $pow, $mod);
		}

		// emulate bcpowmod
		$result = '1';
		do {
			if (!bccomp(bcmod($pow, '2'), '1')) {
				$result = bcmod(bcmul($result, $num), $mod);
			}
		   $num = bcmod(bcpow($num, '2'), $mod);

		   $pow = bcdiv($pow, '2');
		} while (bccomp($pow, '0'));
		return $result;
	}
}