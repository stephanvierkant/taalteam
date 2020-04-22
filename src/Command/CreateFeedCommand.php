<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\Taalteam;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateFeedCommand extends Command
{
    protected static $defaultName = 'app:create';
    private Taalteam $taalteam;

    public function __construct(Taalteam $taalteam)
    {
        parent::__construct();

        $this->taalteam = $taalteam;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->taalteam->writeXML();

        return 0;
    }
}
