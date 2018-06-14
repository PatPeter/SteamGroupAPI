<?php
include('phpseclib/Math/BigInteger.php');    
include('phpseclib/Crypt/RSA.php');
define('CRYPT_RSA_PKCS15_COMPAT', true);  // May not be necessary, but never hurts to be sure.

$url_rsa = 'https://steamcommunity.com/login/getrsakey/?username=';
$username = "";  // Insert bot's username here.
$password = "";  // Insert bot's password here.


// Skip the extra work of POST'ing the data, just GET'ing the url works fine and saves space
$result = file_get_contents($url_rsa . $username);
$result = json_decode($result);

/*if ($result->success){
    echo "Got Info!<br/><br/>";
} else if (!$result->success){            // Remove comment markers during testing
    echo "Unable to grab Info<br/><br/>"; // You can also use this to log errors to a database or email you if needed.
}*/

//echo var_dump($result);
// More testing code, just to help you see what's going on.
//echo "<br/><br/>";

$rsa = new Crypt_RSA();
$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
$key = array(
    'n' => new Math_BigInteger($result->publickey_mod,16),
    'e' => new Math_BigInteger($result->publickey_exp,16) // Fixed base :)
);
$rsa->loadKey($key);
$password = base64_encode($rsa->encrypt($password)); // Steam uses Base64_Encode()

echo "Password Encrypted: " . $password . "<br/><br/>";
// Should look like numbers and letters, any 'weird' characters shouldn't be here

$data = array(
    'username' => $username,
    'password' => $password,
    'twofactorcode'=> "",
    'emailauth'=> "", 
    'loginfriendlyname'=> "", 
    'captchagid'=> "",         // If all goes well, you shouldn't need to worry
    'captcha_text'=> "",       // about Captcha.
    'emailsteamid'=> "", 
    'rsatimestamp'=> $result->timestamp, 
    'remember_login'=> "false" 
);
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);
var_dump($options);

$url="https://steamcommunity.com/login/dologin/";

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$result = json_decode($result);

echo var_dump($result);
// Hopefully everything went O.K. and you should be able pull out tokens
// for usage in your application.
?>