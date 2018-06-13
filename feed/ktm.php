<?php
require('../cfeed.php');

$feed = new Feed("cssktm","ktm");
$feed->PrintRSS(30);
?>