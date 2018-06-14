<?php

require_once __DIR__.'/vendor/autoload.php';

$client = new \Goutte\Client();

$baseUrl = 'https://store.steampowered.com/login/'; // https://steamcommunity.com/login/home
$groupHistoryUrl = 'http://steamcommunity.com/groups/cssktm/history';
//$savedStoriesUrl = $baseUrl . 'saved?id=' . $username;

/** @var $crawler \Symfony\Component\DomCrawler\Crawler */
$crawler = $client->request('GET', $baseUrl);

/** @var $link \Symfony\Component\DomCrawler\Link */
//$link    = $crawler->selectLink('login')->link();
//$crawler = $client->click($link);

$username = '';
$password = '';

/** @var $form \Symfony\Component\DomCrawler\Form */
$form    = $crawler->filter('form[name="logon"]')->first()->form(); // $crawler->selectButton('<span>Sign in</span>')->form();
$crawler = $client->submit($form, array(
    'username' => $username,
    'password' => $password,
));

$errorDisplay    = $crawler->filter('#error_display')->first()->text();
print_r($errorDisplay);

//if (!$client->getResponse()->isRedirect()) die('Form submit failed.');
$crawler = $client->followRedirect();
if (200 != $client->getResponse()->getStatusCode()) die('Redirect failed.');

$html = '';
foreach ($crawler as $domElement) {
    $html .= $domElement->ownerDocument->saveHTML($domElement);
}
echo $html;

/** @var $crawler \Symfony\Component\DomCrawler\Crawler */
$crawler = $client->request('GET', $groupHistoryUrl);

if ($crawler->filter('form[name="historyItem"]')->count() > 0) error_log('found history item');

die();

/** @var $historyItem \Symfony\Component\DomCrawler\Crawler */
$historyItems = $crawler->filter('div');

$historyItems = array();

//foreach ($historyItems as $historyItem) {
//
//}

print_r($historyItems);

/** @var $commentRows \Symfony\Component\DomCrawler\Crawler */
//$commentRows = $crawler->filter('td.subtext a');

/*$stories = array();

foreach ($linkRows as $i => $row) {
    //** @var $row \DOMElement

    $commentLinkPosition = (($i * 2) + 1);

    //** @var $comment \Symfony\Component\DomCrawler\Crawler
    $comment = $commentRows->eq($commentLinkPosition);

    if ($comment->count() > 0 && $row->nodeValue != 'More') {
        $stories[] = array(
            'title'       => $row->nodeValue,
            'link'        => $row->getAttribute('href'),
            'comment'     => $comment->text(),
            'commentLink' => $baseUrl . $comment->attr('href'),
        );
    }
}

print_r($stories);*/
