<?php

namespace Fipe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DefaultCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fipe:default')
            ->setDescription('FIPE Crawler');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('----------------------------------------------------------------------');
        $output->writeln('<info>FIPE Crawler</info>');
        $output->writeln('<comment>Desenvolvido por Rafael Goulart</comment>');
        $output->writeln('<comment>Utilize "./fipecrawler --help" para lista de opções</comment>');
        $output->writeln('----------------------------------------------------------------------');
    }
}