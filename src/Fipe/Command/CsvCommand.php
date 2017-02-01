<?php
/**
 * Fipe Crawler
 * @author Rafael Goulart <rafaelgou@gmail.com>
 */

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

/**
 * Classe CsvCommand
 * Executa a exportação para CSV
 *
 * @category Command
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */
class CsvCommand extends ExtrairVeiculoCommand
{
    /**
     * @var \Fipe\Database
     */
    protected $db;

    /**
     * Configuration
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $help = 'Gera CSV para tabela FIPE informando ano, mês e tipo'.PHP_EOL.'Somente para dados já extraídos para banco de dados locais'.PHP_EOL.''.PHP_EOL.'Sintaxe interativa:'.PHP_EOL.'./fipecrawler csv:veiculo'.PHP_EOL.''.PHP_EOL.'Sintaxe completa'.PHP_EOL.'./fipecrawler csv:veiculo ano mes tipo arquivo'.PHP_EOL;


        $this
            ->setName('veiculo:csv')
            ->setDescription('Exporta arquivo CSV por ano, mês e tipo')
            ->setHelp($help)
            ->addArgument(
                'arquivo',
                InputArgument::REQUIRED,
                'Informe nome do arquivo'
            )
        ;
    }

    /**
     * Interaction
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $helper = $this->getHelper('question');

        $mes  = str_pad($input->getArgument('mes'), 2, '0', STR_PAD_LEFT);
        $ano  = $input->getArgument('ano');
        $tiposRev = array_flip(Crawler::$tipoVeiculosFull);
        $tipoDesc = $input->getArgument('tipo');
        if (!array_key_exists($tipoDesc, $tiposRev)) {
            $this->fatal($output, "Tipo não encontrado: $tipoDesc");
            exit;
        }
        $tipo     = $tiposRev[$tipoDesc];
        $arquivo = "fipe_{$ano}{$mes}_{$tipoDesc}.csv";

        if (!$input->getArgument('arquivo')) {
            $question = new Question(
                "Informe nome do arquivo (padrao '{$arquivo}'): ",
                $arquivo
            );
            $input->setArgument('arquivo', $helper->ask($input, $output, $question));
        }
    }

    /**
     * Execution
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mes  = str_pad($input->getArgument('mes'), 2, '0', STR_PAD_LEFT);
        $ano  = $input->getArgument('ano');
        $tiposRev = array_flip(Crawler::$tipoVeiculosFull);
        $tipoDesc = $input->getArgument('tipo');
        if (!array_key_exists($tipoDesc, $tiposRev)) {
            $this->fatal($output, "Tipo não encontrado: $tipoDesc");
            exit;
        }
        $tipo     = $tiposRev[$tipoDesc];
        $arquivo  = $input->getArgument('arquivo');
        $descTabela = "tabela $mes/$ano, tipo=[{$tipo}] {$tipoDesc}";

        $this->banner($output);
        $output->writeln("");
        $output->writeln("<info>Recuperando veículos para $descTabela...</info>");

        $veiculos = $this->db->findVeiculos($ano, $mes, $tipo);
        if (count($veiculos) === 0) {
            $this->fatal($output, "Nenhum veículo encontrado para $descTabela");
            exit;
        }

        $totalVeiculos = count($veiculos);
        $output->writeln("<info>Encontrados $totalVeiculos veículos para $descTabela</info>");

        $progress = new ProgressBar($output, $totalVeiculos);
        $progress->setFormat(" %current%/%max% [%bar%] veículos exportados");
        $progress->start();
        $content = $this->db->getCsvHeader($veiculos[0], true);
        foreach ($veiculos as $veiculo) {
            $content .= PHP_EOL.$this->db->prepareCsvRow($veiculo, true);
            $progress->advance();
        }
        $progress->finish();

        $output->writeln("");
        $output->writeln("<comment>Exportados $totalVeiculos veículos para $descTabela !</comment>");

        $arquivo = __DIR__.DIRECTORY_SEPARATOR.$arquivo;
        $arquivo = str_replace('/src/Fipe/Command', '', $arquivo);
        $output->writeln("<info>Tentando salvar arquivo $arquivo...</info>");
        file_put_contents($arquivo, $content);
        $output->writeln("<comment>Criado arquivo $arquivo !</comment>");
        $output->writeln("");
    }
}
