<?php

namespace Fipe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\ProgressBar;
use Fipe\Database;
use Fipe\Crawler;
use Symfony\Component\Stopwatch\Stopwatch;

class ExtrairVeiculoCommand extends Command
{
    /**
     * @var \Fipe\Database
     */
    protected $db;

    protected function configure()
    {
        $help = 'Extrai tabela FIPE informando ano, mês e tipo' . PHP_EOL
              . '' . PHP_EOL
              . 'Sintaxe interativa:' . PHP_EOL
              . './fipecrawler extrair:veiculo' . PHP_EOL
              . '' . PHP_EOL
              . 'Sintaxe completa' . PHP_EOL
              . './fipecrawler extrair:veiculo ano mes tipo' . PHP_EOL;
        $this
            ->setName('veiculo:extrair')
            ->setDescription('Extrai tabela por ano, mês e tipo')
            ->setHelp($help)
            ->addArgument(
                'ano',
                InputArgument::REQUIRED,
                'Informe ano'
            )
            ->addArgument(
                'mes',
                InputArgument::REQUIRED,
                'Informe mês (1 a 12)'
            )
            ->addArgument(
                'tipo',
                InputArgument::REQUIRED,
                'Informe tipo (Carro, Moto, Caminhão)'
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->banner($output);

        $helper = $this->getHelper('question');
        $date = new \DateTime();

        if (!$input->getArgument('ano')) {
            $anos = array();
            for ($i = $date->format('Y'); $i >= 2001; $i--) {
                $anos[$i] = $i;
            }
            $question = new ChoiceQuestion(
                'Informe ano (ENTER para ' . $date->format('Y') . ')',
                $anos,
                $date->format('Y')
            );
            $input->setArgument('ano', $helper->ask($input, $output, $question));
        }

        if (!$input->getArgument('mes')) {
            $meses = array();
            foreach (range(1, 12) as $mes) {
                $meses[$mes] = $mes;
            }

            $question = new ChoiceQuestion(
                'Informe mês (1 a 12) (ENTER para ' . $date->format('m') . ')',
                $meses,
                $date->format('m')
            );
            $input->setArgument('mes', $helper->ask($input, $output, $question));
        }

        if (!$input->getArgument('tipo')) {
            $question = new ChoiceQuestion(
                'Informe tipo (1 = carro, 2 = moto, 3 = caminhão) (ENTER para Carro)',
                Crawler::$tipoVeiculosFull,
                1
            );
            $input->setArgument('tipo', $helper->ask($input, $output, $question));
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('progress');

        $mes  = str_pad($input->getArgument('mes'), 2, '0', STR_PAD_LEFT);
        $ano  = $input->getArgument('ano');
        $tiposRev = array_flip(Crawler::$tipoVeiculosFull);
        $tipoDesc = $input->getArgument('tipo');
        if (!array_key_exists($tipoDesc, $tiposRev)) {
            $this->fatal($output, "Tipo não encontrado: $tipoDesc");
            exit;
        }
        $tipo     = $tiposRev[$tipoDesc];

        $crawler = new Crawler();

        $output->writeln("");
        $output->writeln("<info>Recuperando tabelas para $mes/$ano...</info>");
        $tabela = $crawler->getTabelaByAnoMes($ano, $mes);
        if (null === $tabela) {
            $this->fatal($output, "Não encontrada tabela para $mes/$ano");
            exit;
        }
        $output->writeln("<comment>Encontrada tabela $mes/$ano !</comment>");
        $output->writeln("");

        $descTabela = "tabela id=[{$tabela['id']}] $mes/$ano, tipo=[{$tipo}] {$tipoDesc}";
        $output->writeln("<info>Recuperando marcas para $descTabela...</info>");
        $marcas = $crawler->extractMarcas($tabela['id'], $tipo);
        $totalMarcas = count($marcas['results']);
        if ($totalMarcas === 0) {
            $this->fatal($output, "Não encontrada nenhuma marca para $descTabela !");
            exit;
        }
        $output->writeln("<comment>Encontradas {$totalMarcas} marcas para $descTabela !</comment>");
        $output->writeln("");

        $output->writeln("<info>Recuperando modelos para {$totalMarcas} marcas -- $descTabela...</info>");
        $output->writeln("");
        $totalModelos = 0;
        $progress = new ProgressBar($output, $totalMarcas);
        $progress->setFormat(" %current%/%max% [%bar%] %ttmod% modelos extraídos");
        $progress->setMessage($totalModelos, 'ttmod');
        $progress->start();
        $modelos = array();
        foreach ($marcas['results'] as $marca) {
            $tmpModelos = $crawler->extractModelos($tabela['id'], $tipo, $marca['id']);
            $modelos[$marca['id']] = $tmpModelos['results'];
            $totalModelos += count($tmpModelos['results']);
            $progress->setMessage($totalModelos, 'ttmod');
            $progress->advance();
        }
        $progress->finish();
        $output->writeln("");
        $output->writeln("<comment>Encontrados {$totalModelos} modelos para {$totalMarcas} marcas -- $descTabela !</comment>");
        $output->writeln("");

        $output->writeln("<info>Recuperando veiculos para para {$totalModelos} -- $descTabela...</info>");
        $totalVeiculos = 0;
        $progress = new ProgressBar($output, $totalModelos);
        $progress->setFormat(" %current%/%max% [%bar%] %ttvei% veículos extraídos");
        $progress->setMessage($totalVeiculos, 'ttvei');
        $progress->start();
        foreach($modelos as $marcaId => $marcaModelos) {
            foreach($marcaModelos as $modelo) {
                $tmpVeiculos  = $crawler->extractVeiculos($tabela['id'], $tipo, $marcaId, $modelo['id'], true);
                $this->db->saveVeiculoCompletos($tmpVeiculos);
                $totalVeiculos += $tmpVeiculos['veiculosTotal'];
                $progress->setMessage($totalVeiculos, 'ttvei');
                $progress->advance();
            }
        }
        $progress->finish();

        $output->writeln("");
        $output->writeln("<comment>Extraídos $totalVeiculos veículos -- $descTabela !</comment>");
        $output->writeln("");

        $event = $stopwatch->stop('progress');
        $duration = $this->seconds2human($event->getDuration());
        $memory   = $this->memory2human($event->getMemory());
        $this->alert($output, "FIPE Crawler executado com sucesso em {$duration}, memória {$memory}");

        $output->writeln("<question>FIPE Crawler executado com sucesso!</question>");
        $output->writeln("");
    }

    public function setDb(Database $db)
    {
        $this->db = $db;
    }

    public function fatal(OutputInterface $output, $msg)
    {
        $dash  = str_repeat('-', 80);
        $space = str_repeat(' ', 80);
        $error = str_pad('** ERRO FATAL **', 80, ' ', STR_PAD_RIGHT);
        $output->writeln("");
        $output->writeln("<error>$dash</error>");
        $output->writeln("<error>$space</error>");
        $output->writeln("<error>$error</error>");
        $output->writeln("<error>$space</error>");
        $msg = str_pad($msg, 80, ' ', STR_PAD_RIGHT);
        $output->writeln("<error>$msg</error>");
        $output->writeln("<error>$space</error>");
        $output->writeln("<error>$dash</error>");
        exit;
    }

    public function alert(OutputInterface $output, $msg)
    {
        $dash  = str_repeat('-', 80);
        $space = str_repeat(' ', 80);
        $msg = str_pad($msg, 80, ' ', STR_PAD_RIGHT);

        $output->writeln("<question>$dash</question>");
        $output->writeln("<question>$space</question>");
        $output->writeln("<question>$msg</question>");
        $output->writeln("<question>$space</question>");
        $output->writeln("<question>$dash</question>");

    }
    public function banner(OutputInterface $output)
    {
        $dash  = str_repeat('-', 80);
        $space = str_repeat(' ', 80);

        $output->writeln("<question>$dash</question>");
        $output->writeln("<question>$space</question>");

        $msg = str_pad('  ' . $this->getApplication()->getName(), 80, ' ', STR_PAD_RIGHT);
        $output->writeln("<question>$msg</question>");

        $msg = str_pad('  ' . $this->getName(), 80, ' ', STR_PAD_RIGHT);
        $output->writeln("<question>$msg</question>");

        $msg = str_pad('  ' . $this->getDescription(), 80, ' ', STR_PAD_RIGHT);
        $output->writeln("<question>$msg</question>");

        $output->writeln("<question>$space</question>");
        $output->writeln("<question>$dash</question>");
    }

    public function seconds2human($seconds)
    {
        $s = $seconds % 60;
        $m = floor(($seconds % 3600) / 60);
        $h = floor(($seconds % 86400) / 3600);

        return "{$h}h{$m}m{$s}s";
    }

    public function memory2human($memory)
    {
        if ($memory < 1024) {
            return $memory . " bytes";
        } elseif ($memory < 1048576) {
            return round($memory/1024,2)." kilobytes";
        } else {
            return round($memory/1048576,2)." megabytes";
        }

    }

}