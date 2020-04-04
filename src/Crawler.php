<?php declare(strict_types=1);

namespace Crawler;

use DOMDocument;
use DOMNodeList;
use DOMNode;

class Crawler
{
    private array $index = [];
    private array $queue = [];
    private array $errors = [];
    private $streamContext;
    private int $limit;

    public function __construct(string $startUrl, array $contextOptions, int $limit)
    {
        array_push($this->queue, $startUrl);
        $this->streamContext = stream_context_create($contextOptions);
        $this->limit = $limit;
    }

    public function getIndex(): array
    {
        return $this->index;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function crawl(): void
    {
        $url = $this->getFirstUrlFromQueue();

        if ($url) {
            $content = $this->getPageContent($url);

            if ($content) {
                $links = $this->parseLinksFromPageContent($content);
                
                array_push($this->index, $url);
                $this->addLinksToQueue($links, $this->getBaseFromUrl($url));
            } else {
                array_push($this->errors, $url);
            }

            if (!$this->isIndexLimitExceeded()) {
                $this->crawl();
            }
        }
    }

    private function getBaseFromUrl(string $url): string
    {
        $parsedUrl = parse_url($url);

        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}";
    }

    private function isIndexLimitExceeded(): bool
    {
        return $this->limit !== 0 && count($this->index) > $this->limit;
    }

    private function addLinksToQueue(DOMNodeList $links, string $urlBase): void
    {
        foreach ($links as $link) {
            $this->addLinkToQueue($link, $urlBase);
        }

        $this->queue = array_unique($this->queue);
    }

    private function addLinkToQueue(DOMNode $link, string $urlBase): void
    {
        $href = $link->getAttribute('href');
        $rootPath = '/';

        if ($href !== $rootPath) {
            if (strpos($href, $rootPath) === 0) {
                $href = $urlBase . $href;
            }
            
            array_push($this->queue, $href);
        }
    }

    private function parseLinksFromPageContent(string $content): DOMNodeList
    {
        $dom = new DOMDocument();
        @$dom->loadHtml(mb_convert_encoding($content, 'UTF-8'));

        return $dom->getElementsByTagName('a');
    }

    private function getPageContent(string $url): ?string
    {
        $pageContent = null;

        try {
            $content = file_get_contents($url, false, $this->streamContext);

            if ($content !== false) {
                $pageContent = $content;
            }
        } catch (Exception $e) {
            echo $e;
        }

        return $pageContent;
    }

    private function getFirstUrlFromQueue(): ?string
    {
        return array_shift($this->queue);
    }
}
