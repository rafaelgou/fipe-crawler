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
 * Classe JavascriptCommand
 *
 * Concatena dependências em um único arquivo
 *
 * @deprecated (melhor utilizar gulp/grunt ou similar para isto)
 * @category Command
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */
class JavascriptCommand extends Command
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

        $help = 'Gera javascript final para aplicação'.PHP_EOL.'./fipecrawler javascript:compile'.PHP_EOL;

        $this
            ->setName('javascript:combine')
            ->setDescription('Gera javascript final para aplicação')
            ->setHelp($help)
        ;
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
        $files = array(
            'bower_components/async/lib/async.js',
            'bower_components/angular/angular.js',
            'bower_components/angular-route/angular-route.js',
            'bower_components/angular-resource/angular-resource.js',
            'bower_components/angular-bootstrap/ui-bootstrap.js',
            'bower_components/angular-bootstrap/ui-bootstrap-tpls.js',
            'bower_components/angular-sanitize/angular-sanitize.js',
            'bower_components/ng-csv/build/ng-csv.js',
            'web/app/FipeCrawlerApp.js',
            'web/app/errors/ErrorController.js',
            'web/app/main/MainController.js',
            'web/app/extract/ExtractController.js',
            'web/app/extract/ResourceModel.js',
            'web/app/modal/ModalController.js',
        );
        $dir = __DIR__.'/../../../';
        $content = '';

        foreach ($files as $file) {
            $content .= file_get_contents($dir.$file);
        }

        file_put_contents($dir.'web/js/app.js', $content);
        $output->writeln("Javascript gerado em <info>web/js/app.js</info>");
    }
}
