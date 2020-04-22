<?php

declare(strict_types=1);

namespace App\Services;

use Generator;
use Laminas\Feed\Writer\Entry;
use Laminas\Feed\Writer\Feed;
use LogicException;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\HttpClient;
use function strpos;
use function time;

class Taalteam
{
    public const BASEURL = 'https://www.nporadio1.nl';

    public function writeXML() : void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile('public/feed.xml', $this->generateXML());
    }

    private function generateXML()
    {
        $feed = new Feed();
        $feed->setTitle("Spraakmakers' Taalteam");
        $feed->setDateModified(time());
        $feed->setLink('https://stephanvierkant.nl/taalteam.xml');
        $feed->setDescription('Taalteam');
        $feed->setCopyright('NPO Radio 1, KRO-NRCV');

        foreach ($this->getUrls() as $url) {
            $page = $this->getCrawler($url);

            $entry = $this->getEntry($feed, $page);

            $feed->addEntry($entry);
        }

        return $feed->export('rss');
    }

    private function getUrls() : Generator
    {
        $crawler = $this->getCrawler('/spraakmakers');

        $items = $crawler
            ->filter('.broadcast-item a')
            ->reduce(static fn (Crawler $node, $i) => strpos($node->text(), 'Taalteam') !== false)
        ;

        foreach ($items as $item) {
            $a = $item->attributes->item(0);

            yield $a->nodeValue;
        }
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
        ;

        foreach ($sources as $source) {
            return $source->attributes->item(0)->nodeValue;
        }

        throw new LogicException('Niets gevonden!');
    }

    private function getCrawler(string $url) : Crawler
    {
        $browser = new HttpBrowser(HttpClient::create());

        return $browser->request('GET', self::BASEURL . $url);
    }
}
