<?php

declare(strict_types=1);

namespace App\Services;

use Laminas\Feed\Writer\Feed;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;

use function strpos;
use function time;

final class DagvanDiederik extends Scraper
{
    public const BASEURL = 'https://www.nporadio1.nl';
    public const PATH = '/spraakmakers';

    public function writeXML(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->projectDir . '/public/taalteam.xml', $this->generateXML());
    }

    private function generateXML()
    {
        $feed = new Feed();
        $feed->setTitle("Spraakmakers' Taalteam");
        $feed->setDateModified(time());
        $feed->setLink('https://taalteam.stephanvierkant.nl/taalteam.xml');
        $feed->setDescription('Taalteam');
        $feed->setCopyright('NPO Radio 1, KRO-NRCV');

        foreach ($this->getUrls() as $url) {
            $page = $this->getCrawler($url);

            $entry = $this->getEntry($feed, $page);

            $feed->addEntry($entry);
        }

        return $feed->export('rss');
    }
}
