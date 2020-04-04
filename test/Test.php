<?php declare(strict_types=1);

require_once('../src/Crawler.php');

use Crawler\Crawler;

$contextOptions = array(
    'http' => array(
        'method'=>'GET',
        'ignore_errors' => true,
        'user_agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'
    )
);

$crawler = new Crawler('https://www.php.net/', $contextOptions, 200);

$crawler->crawl();
print_r($crawler->getIndex());
