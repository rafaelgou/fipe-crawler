<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');

use Symfony\Component\HttpFoundation\Request;
use Fipe\Controller;

$request    = Request::createFromGlobals();
$db         = new Fipe\Database($db['host'], $db['dbname'], $db['user'], $db['pass']);
$controller = new Controller($request, $db);
$action     = $request->get('action', '404');
$tabela = $request->get('tabela', null);
$tipo   = $request->get('tipo', null);
$marca  = $request->get('marca', null);
$modelo = $request->get('modelo', null);


switch ($action) {

    case 'index':
        $response = $controller->indexAction();
        break;

    case 'tabelas':
        $response = $controller->tabelasAction();
        break;

    case 'extract_marcas':
        $response = $controller->extractMarcasAction($tabela, $tipo);
        break;

    case 'extract_modelos':
        $response = $controller->extractModelosAction($tabela, $tipo, $marca);
        break;

    case 'extract_veiculos':
        $response = $controller->extractVeiculosAction($tabela, $tipo, $marca, $modelo);
        break;

    case 'extract_modelos_veiculos':
        $response = $controller->extractModelosVeiculosAction($tabela, $tipo, $marca);
        break;

    case 'csv_tabelas':
        $response = $controller->csvTabelasAction();
        break;

    case 'csv_veiculos':
        $response = $controller->csvVeiculosAction($tabela, $tipo);
        break;

    default:
    case '404':
        $response = $controller->error404Action();
        break;

}

$response->prepare($request);
$response->send();