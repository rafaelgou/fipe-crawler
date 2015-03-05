<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');
$tabId   = 175;
$tipoVei = 1;

$crawler = new Fipe\Crawler;
$db      = new Fipe\Database($db['host'], $db['dbname'], $db['user'], $db['pass']);

// $tabelas = $crawler->getTabelas();
// $db->atualizaPeriodos($tabelas);

// $marcas = $crawler->getMarcas($tabId, $tipoVei);
// $db->atualizaMarcas($marcas, $tabId, $tipoVei);

$modelos = $crawler->getModelos($tabId, $tipoVei, 21); // 21 = Fiat
$db->atualizaModelos($modelos, 21);

/*

$marcas = $crawler->getMarcas($tabela, $tipoVeiculo);
foreach ($marcas as $marca) {
    foreach ($marcas as $marca) {
        $modelos = $crawler->getModelos($tabela, $tipoVeiculo, $marca->Value);
        foreach ($modelos as $modelo) {
            $anoModelos = $crawler->getAnoModelos(
                $tabela, $tipoVeiculo, $marca->Value, $modelo->Value
            );
            foreach ($anoModelos as $anoModelo) {
                echo "{$marca->Label} - {$modelo->Label} - {$anoModelo->Label}" . PHP_EOL;
                // $tmpValue    = explode('-', $anoModelo->Value);
                // $ano         = $tmpValue[0];
                // $combustivel = $tmpValue[1];
                // $veiculos[] = $crawler->getVeiculo(
                //     $tabela, $tipoVeiculo, $marca->Value,
                //     $modelo->Value, $combustivel, $ano
                // );
            }
        }
    }

}
*/
