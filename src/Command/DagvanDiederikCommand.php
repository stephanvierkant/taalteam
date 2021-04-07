<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\DagvanDiederik;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DagvanDiederikCommand extends Command
{
    protected static $defaultName = 'app:diederik';
    private DagvanDiederik $dagvanDiederik;

    public function __construct(DagvanDiederik $dagvanDiederik)
    {
        parent::__construct();

        $this->dagvanDiederik = $dagvanDiederik;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dagvanDiederik->writeXML();

        $output->writeln('Done!');

        return 0;
    }
}
