<?php

declare(strict_types=1);

namespace App\Services;

use Laminas\Feed\Writer\Feed;
use Symfony\Component\Filesystem\Filesystem;

use function time;

final class DagvanDiederik extends Scraper
{
    public const BASEURL      = 'https://www.nporadio1.nl';
    protected string $path    = '/dit-is-de-dag';
    protected string $keyword = 'Dit is de dag van Diederik';

    public function writeXML(): void
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($this->projectDir . '/public/diederik.xml', $this->generateXML());
    }

    private function generateXML()
    {
        $feed = new Feed();
        $feed->setTitle('Dit is de dag van Diederik');
        $feed->setDateModified(time());
        $feed->setLink('https://taalteam.stephanvierkant.nl/diederik.xml');
        $feed->setDescription('Dit is de dag');
        $feed->setCopyright('NPO Radio 1, EO');

        foreach ($this->getUrls() as $url) {
            $page = $this->getCrawler($url);

            $entry = $this->getEntry($feed, $page);

            $feed->addEntry($entry);
        }

        return $feed->export('rss');
    }
}
