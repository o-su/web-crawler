# web-crawler

Web crawler written in PHP

## Example Usage

```php
<?php declare(strict_types=1);

require_once('../src/Crawler.php');

use Crawler\Crawler;

// see ./test/Test.php
$crawler = new Crawler($url, $contextOptions, $limit);

$crawler->crawl();
print_r($crawler->getIndex());
```
