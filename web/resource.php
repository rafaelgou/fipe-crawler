<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');

use Symfony\Component\HttpFoundation\Request;
use Fipe\Controller;

$request    = Request::createFromGlobals();
$db         = new Fipe\Database($db['host'], $db['dbname'], $db['user'], $db['pass']);
$controller = new Controller($request, $db);
$action     = $request->get('action', '404');
$tabelaId   = $request->get('tabela', null);
$tipo       = $request->get('tipo', null);
$marcaId    = $request->get('marca', null);
$modeloId   = $request->get('modelo', null);


switch ($action) {

    case 'index':
        $response = $controller->indexAction();
        break;

    case 'tabelas':
        $response = $controller->tabelasAction();
        break;

    case 'extract_marcas':
        $response = $controller->extractMarcasAction($tabelaId, $tipo);
        break;

    case 'extract_modelos':
        $response = $controller->extractModelosAction($tabelaId, $tipo, $marcaId);
        break;

    case 'extract_veiculos':
        $response = $controller->extractVeiculosAction($tabelaId, $tipo, $marcaId, $modeloId);
        break;

    case 'extract_modelos_veiculos':
        $response = $controller->extractModelosVeiculosAction($tabelaId, $tipo, $marcaId);
        break;

    case 'csv_tabelas':
        $response = $controller->csvTabelasAction();
        break;

    case 'csv_veiculos':
        $response = $controller->csvVeiculosAction($tabelaId, $tipo);
        break;

    default:
    case '404':
        $response = $controller->error404Action();
        break;

}

$response->prepare($request);
$response->send();
