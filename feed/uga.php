<?php
require('../cfeed.php');

$feed = new Feed('unigamia','uga');
$feed->PrintRSS(30);
?>