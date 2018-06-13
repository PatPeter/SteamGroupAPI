<?php
require('../Autoloader.php');

$feed = new \libsteam\group\history\Feed("cssktm","ktm");
$feed->PrintRSS(30);
?>