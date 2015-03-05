<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Fipe\Controller;
use Fipe\Database;

$request    = Request::createFromGlobals();
$db         = new Fipe\Database($db['host'], $db['dbname'], $db['user'], $db['pass']);
$controller = new Controller($request, $db);
$action     = $request->get('action', '404');

switch ($action) {

    case 'index':
        $response = $controller->indexAction();
        break;

    case 'tabelas':
        $response = $controller->tabelasAction();
        break;

    case 'extract_marcas':
        $tabela = $request->get('tabela', null);
        $tipo   = $request->get('tipo', null);
        $response = $controller->extractMarcasAction($tabela, $tipo);
        break;

    case 'extract_modelos':
        $tabela = $request->get('tabela', null);
        $tipo   = $request->get('tipo', null);
        $marca  = $request->get('marca', null);
        $response = $controller->extractModelosAction($tabela, $tipo, $marca);
        break;

    case 'extract_veiculos':
        $tabela = $request->get('tabela', null);
        $tipo   = $request->get('tipo', null);
        $marca  = $request->get('marca', null);
        $modelo = $request->get('modelo', null);
        $response = $controller->extractVeiculosAction($tabela, $tipo, $marca, $modelo);
        break;

    default:
    case '404':
        $response = $controller->error404Action();
        break;

}

$response->prepare($request);
$response->send();