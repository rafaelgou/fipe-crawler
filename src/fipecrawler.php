<?php

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../config/config.php');

use Fipe\Command\ExtrairVeiculoCommand;
use Fipe\Command\CsvCommand;
use Fipe\Command\JavascriptCommand;
use Symfony\Component\Console\Application;
use Fipe\Database;

$db = new Database($db['host'], $db['dbname'], $db['user'], $db['pass']);

$app = new Application();
$app->setName('FIPE Crawler');

$extVeiCommand = new ExtrairVeiculoCommand();
$extVeiCommand->setDb($db);
$app->add($extVeiCommand);

$csvCommand = new CsvCommand();
$csvCommand->setDb($db);
$app->add($csvCommand);

$app->add(new JavascriptCommand());

$app->run();
