<?php

declare(strict_types=1);

namespace App\Services;

use Laminas\Feed\Writer\Entry;
use Laminas\Feed\Writer\Feed;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;

use function strpos;

class Scraper
{
    public const BASEURL = 'https://www.nporadio1.nl';
    public const PATH    = '';

    protected string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function writeXML(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->projectDir . '/public/feed.xml', $this->generateXML());
    }

    protected function getCrawler(string $url): Crawler
    {
        $browser = new HttpBrowser(HttpClient::create());

        return $browser->request('GET', self::BASEURL . $url);
    }

    protected function getUrls(): array
    {
        $crawler = $this->getCrawler(self::PATH);

        return $crawler
            ->filter('.broadcast-item a')
            ->reduce(static fn (Crawler $node, $i) => strpos($node->text(), 'Taalteam') !== false)
            ->each(static fn (Crawler $node, $i) => $node->attr('href'));
    }

    protected function getEntry(Feed $feed, Crawler $crawler): Entry
    {
        $title       = $crawler->filterXpath("//meta[@property='og:title']")->extract(['content'])[0];
        $description = $crawler->filterXpath("//meta[@property='og:title']")->extract(['content'])[0];

        return $feed->createEntry()
            ->setTitle($title)
            ->setDescription($description)
            ->setLink($this->getFilename($crawler));
    }

    private function getFilename(Crawler $page, string $format = 'audio/mp3'): string
    {
        $sources = $page
            ->filter('source')
            ->reduce(static fn (Crawler $node, $i) => $node->attr('type') === $format)
            ->reduce(static fn (Crawler $node, $i) => $node->attr('src') !== '/')
            ->each(static fn (Crawler $node, $i) => $node->attr('src'))
        ;

        return $sources[0];
    }
}
