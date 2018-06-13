<?php
namespace libsteam\common;

// Stevish RSA version 2.1

// Copyright 2009, Stevish.com (with mad props to te developers of the PEAR RSA extension)
// This script is distributed under the terms of the GNU General Public License (GPL)
// See http://www.gnu.org/licenses/gpl.txt for license details

// Please use, distribute, modify, rip-off, sell or destroy this script however you see fit
// I only ask that you remove my copyright if you modify and re-release this.
// To make sure you have the genuine, up-to-date version, visit http://stevish.com/rsa-encryption-in-pure-php

/**
 * Created on 2013/01/05 at 03:30:02 PM.
 */
class RSAKeyGen {

	static $primes = null;
	
	function __construct() {
		if(is_null($this->primes)) {
			//Make $this->primes an array of all primes under 20,000
			//We will use this list to rule out the "easy" composite (non-prime) numbers
			
			for ($i = 0; $i < 20000; $i++) {
				$numbers[] = $i;
			}
			$numbers[0] = $numbers[1] = 0; //Zero and one are not primes :)
			foreach ($numbers as $i => $num) {
				if(!$num) {
					continue;
				}
				$j = $i;
				
				for ($j += $i; $j < 20000; $j += $i) {
					//Jump to each multiple of the current number and set it to 0 (not prime)
					$numbers[$j] = 0;
				}
			}
			foreach($numbers as $num) {
				//Take all the prime numbers and fill the primes array
				if ($num) {
					$this->primes[] = $num;
				}
			}
		}
	}
	
	function make_keys($bits = 1024, $u = false, $v = false) {
		//If not provided, select 2 random prime numbers each at about half the bit size of our key
		//We keep a possible variant of 2 bits so that there are a wider range of primes that can be used
		$variant = rand(0,2);
		if(!$u)
			$u = $this->make_prime(ceil($bits/2) + $variant);
		if(!$v) 
			$v = $this->make_prime(floor($bits/2) - $variant);
		while(substr($u, -16, 2) < (substr($v, -16, 2) + 2) && substr($u, -16, 2) > (substr($v, -16, 2) - 2) ) {
			//Make sure the 2 primes are at least 1 quadrillion numbers apart
			$v = $pm->make_prime(intval($digits/2));
		}
		
		//Find our modulo r and phi(r)
		$r = bcmul($u, $v);
		$phir = bcmul(bcsub($u, 1), bcsub($v, 1));
		
		//Pick a value for p (The Public key). We will make it 17 bits or smaller.
		$psize = ($bits > 51) ? 17 : intval($bits/3);
		$p = $this->make_prime($psize);
		
		//Find the inverse of p mod phi(r) using the Extended Euclidian Algorithm
		$q = $this->euclid($p, $phir);

		return array($p, $q, $r);
	}
	
	function make_prime($bits) {
		//This function should not be used to generate primes less than 18 bits

		$min = bcpow(2, $bits - 1);
		$max = bcsub(bcmul($min, 2), 1);
		$digits = strlen($max);
		while(strlen($min) < $digits)
			$min = "0" . $min;
		$ent = $this->entropyarray($digits);
		$maxed = true;
		$mined = true;
		$num = '';
		for($i = 0; $i < $digits; $i++) {
			//Create a long integer between $min and $max starting with the entropy number
			$thismax = 9;
			$thismin = 0;
			if($maxed)
				$thismax = substr($max, $i, 1);
			if($mined)
				$thismin = substr($min, $i, 1);
			
			//Add random numbers (mod 10) until the number meets the constraints
			$thisdigit = ($ent[$i] + rand(0,9)) % 10;
			if($i == $digits - 1) //The last digit should be a 1, 3, 7 or 9
				while($thisdigit != 1 && $thisdigit != 3 && $thisdigit != 7 && $thisdigit != 9 && $thisdigit <= $thismax && $thisdigit >= $thismin)
					$thisdigit = ($thisdigit + rand(0,9)) % 10;
			else
				while($thisdigit <= $thismax && $thisdigit >= $thismin)
					$thisdigit = ($thisdigit + rand(0,9)) % 10;
			$num .= $thisdigit;
			if($maxed && $thisdigit < $thismax)
				$maxed = false;
			if($mined && $thisdigit > $thismin)
				$mined = false;
		}
		
		//Check if the number is prime
		while(!$this->is_prime($num)) {
			//If the number is not prime, add 2 or 4 (since it is currently an odd number)
			//This will keep the number odd and skip 5 to speed up the primality testing
			if(substr($num, -1, 1) == 3)
				$num = bcadd($num, 4);
			else
				$num = bcadd($num, 2);
			$tries++;
		}
		return $num;
	}
	
	function entropyarray($digits) {
		//create a long number based on as much entropy as possible
		$a = base_convert(md5(microtime()), 16, 10);
		$b = base_convert(sha1(@exec('uptime')), 16, 10);
		$c = mt_rand();
		$d = disk_total_space("/");
		$e = rand();
		$f = memory_get_usage();
		
		//Make sure it is only numbers, scramble it and make it the right length
		$num = str_shuffle(preg_replace("[^0-9]", '', $a . $b . $c . $d . $e));
		if(strlen($num) > $digits)
			$num = substr($num, 0, $digits);
		else
			while(strlen($num) < $digits)
				$num = str_shuffle(substr(base_convert(md5($num), 16, 10), 3, 1) . $num);	
		
		//Turn the number into an array and return it
		$ent_array = str_split($num);
		return $ent_array;
	}
	
	function is_prime($num) {
		if(bccomp($num, 1) < 1)
			return false;
		//Clear the easy stuff (divide by all primes under 20,000)
		foreach($this->primes as $prime) {
			if(bccomp($num, $prime) == 0)
				return true;
			if(!bcmod($num, $prime))
				return false;
		}
		
		//Try the more complex method with the first 7 primes as bases
		for($i = 0; $i < 7; $i++) {
			if(!$this->_millerTest($num, $this->primes[$i]))
				return false; //Number is composite
		}
		
		//Strong probability that the number is prime
		return true;
	}
	
	function _millerTest($num, $base) {
		if(!bccomp($num, '1')) {
			// 1 is not prime ;)
			return false;
		}
		$tmp = bcsub($num, '1');

		$zero_bits = 0;
		while (!bccomp(bcmod($tmp, '2'), '0')) {
			$zero_bits++;
			$tmp = bcdiv($tmp, '2');
		}

		$tmp = $this->powmod($base, $tmp, $num);
		if (!bccomp($tmp, '1')) {
			// $num is probably prime
			return true;
		}

		while ($zero_bits--) {
			if (!bccomp(bcadd($tmp, '1'), $num)) {
				// $num is probably prime
				return true;
			}
			$tmp = $this->powmod($tmp, '2', $num);
		}
		// $num is composite
		return false;
	}
	
	function euclid($num, $mod)	{
		//The Extended Euclidian Algorithm
		$x = '1';
		$y = '0';
		$num1 = $mod;
		do {
			$tmp = bcmod($num, $num1);
			$q = bcdiv($num, $num1);
			$num = $num1;
			$num1 = $tmp;
 
			$tmp = bcsub($x, bcmul($y, $q));
			$x = $y;
			$y = $tmp;
		} while (bccomp($num1, '0'));
		if (bccomp($x, '0') < 0) {
			$x = bcadd($x, $mod);
		}
		return $x;
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