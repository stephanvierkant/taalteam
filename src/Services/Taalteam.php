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
use function time;

class Taalteam
{
    public const BASEURL = 'https://www.nporadio1.nl';

    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function writeXML() : void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->projectDir . '/public/feed.xml', $this->generateXML());
    }

    private function generateXML()
    {
        $feed = new Feed();
        $feed->setTitle("Spraakmakers' Taalteam");
        $feed->setDateModified(time());
        $feed->setLink('https://taalteam.stephanvierkant.nl/feed.xml');
        $feed->setDescription('Taalteam');
        $feed->setCopyright('NPO Radio 1, KRO-NRCV');

        foreach ($this->getUrls() as $url) {
            $page = $this->getCrawler($url);

            $entry = $this->getEntry($feed, $page);

            $feed->addEntry($entry);
        }

        return $feed->export('rss');
    }

    private function getUrls() : array
    {
        $crawler = $this->getCrawler('/spraakmakers');

        return $crawler
            ->filter('.broadcast-item a')
            ->reduce(static fn (Crawler $node, $i) => strpos($node->text(), 'Taalteam') !== false)
            ->each(static fn (Crawler $node, $i) => $node->attr('href'));
    }

    private function getEntry(Feed $feed, Crawler $crawler) : Entry
    {
        $title       = $crawler->filterXpath("//meta[@property='og:title']")->extract(['content'])[0];
        $description = $crawler->filterXpath("//meta[@property='og:title']")->extract(['content'])[0];

        return $feed->createEntry()
            ->setTitle($title)
            ->setDescription($description)
            ->setLink('https:' . $this->getFilename($crawler));
    }

    private function getFilename(Crawler $page, string $format = 'audio/mp3') : string
    {
        $sources = $page
            ->filter('source')
            ->reduce(static fn (Crawler $node, $i) => $node->attr('type') === $format)
            ->reduce(static fn (Crawler $node, $i) => $node->attr('src') !== '/')
            ->each(static fn (Crawler $node, $i) => $node->attr('src'))
        ;

        return $sources[0];
    }

    private function getCrawler(string $url) : Crawler
    {
        $browser = new HttpBrowser(HttpClient::create());

        return $browser->request('GET', self::BASEURL . $url);
    }
}
