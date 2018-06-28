<?php

require_once 'vendor/autoload.php';
require_once 'Autoloader.php';

use \phpseclib\Crypt\RSA;
use \phpseclib\Math\BigInteger;
use \Curl\Curl;
use \SteamGroupAPI\Common\Database;
use \SteamGroupAPI\Common\Authenticator;
use \SteamGroupAPI\History\Feed;
use \SteamGroupAPI\History\HistoryItem;

error_reporting(E_ERROR | E_PARSE);

//$content = file_get_contents("Steam Community __ Group __ Universal Gaming Alliance.html");
//echo $content;

/*define('CRYPT_RSA_PKCS15_COMPAT', true);  // May not be necessary, but never hurts to be sure.

$url_rsa = 'https://steamcommunity.com/login/getrsakey/?username=';
$username = "";  // Insert bot's username here.
$password = "";  // Insert bot's password here.


// Skip the extra work of POST'ing the data, just GET'ing the url works fine and saves space
$result = file_get_contents($url_rsa . $username);
$result = json_decode($result);

if ($result->success){
    echo "Got Info!<br/><br/>";
} else if (!$result->success){            // Remove comment markers during testing
    echo "Unable to grab Info<br/><br/>"; // You can also use this to log errors to a database or email you if needed.
}

//echo var_dump($result);
// More testing code, just to help you see what's going on.
//echo "<br/><br/>";

$rsa = new RSA();
$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
$key = array(
    'n' => new BigInteger($result->publickey_mod,16),
    'e' => new BigInteger($result->publickey_exp,16) // Fixed base :)
);
$rsa->loadKey($key);
$password = base64_encode($rsa->encrypt($password)); // Steam uses Base64_Encode()

//echo "Password Encrypted: " . $password . "<br/><br/>";
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

$url="https://steamcommunity.com/login/dologin/";

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
$result = json_decode($result);
print_r($result);*/

/*$curl = SteamGroupAPI\Common\Authentication::login("", "");
if ($curl === false) {
	die("Could not log into Steam.");
}
$content = $curl->get('https://steamcommunity.com/groups/unigamia/history');
error_log($content);*/

$db = new Database();
$last_row = $db->get_last_row();
var_dump($last_row);
if ($last_row === false) {
	die("No database.");
}

$doc = new \DOMDocument();
$doc->loadHtmlFile("Steam Community   Group   Universal Gaming Alliance   Page 13.html");
//$doc->loadHTML($content);

$feed = new Feed("unigamia", "uga", 2014);
//$history_item = new HistoryItem();
//$page_number = 23;

$last_page = $feed->GetLastPage($doc);

error_log("Starting from last page..." . $last_page);

$total_items = $feed->GetItemCount($doc);

error_log("With total items..." . $total_items);

$history_items = $feed->ParsePage($doc, 0, 0);

$i = null;
$located = false;
for ($i = 0; $i < count($history_items); $i++) {
	$history_item = $history_items[$i];
	error_log($history_item->type_id . '==' . $last_row->type_id);
	error_log($history_item->month . '==' . $last_row->month);
	error_log($history_item->day . '==' . $last_row->day);
	error_log($history_item->time . '==' . $last_row->time);
	if ($history_item->type_id == $last_row['type_id'] && 
		$history_item->month == $last_row['month'] && 
		$history_item->day == $last_row['day'] && 
		$history_item->time == $last_row['time'])
	{
		$located = $i;
	} else {
		
	}
}

if ($located !== false) {
	print_r($located);
	$history_items = array_slice($history_items, $i);
	//print_r($history_items);
}


$feed->Update($doc, $history_items);
