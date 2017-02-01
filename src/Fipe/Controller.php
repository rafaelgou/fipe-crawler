<?php
/**
 * Fipe Crawler
 *
 * @category Controller
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */

namespace Fipe;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Classe Controller
 *
 * @category Crawler
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */
class Controller
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    protected $request = null;

    /**
     * @var Database|null
     */
    protected $db = null;

    /**
     * @var Crawler|null
     */
    protected $crawler = null;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session|null
     */
    protected $session = null;

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch|null
     */
    protected $stopwatch = null;

    /**
     * Construtor
     *
     * @param Request  $request Requisição
     * @param Database $db      Banco de dados
     *
     * @return void
     */
    public function __construct(Request $request, Database $db)
    {
        $this->request   = $request;
        $this->db        = $db;
        $this->crawler   = new Crawler();
        $this->stopwatch = new Stopwatch();
        $this->session   = new Session();
        $this->session->start();
        $this->session->set('name', 'FipeCrawler');
    }

    /**
     * Ação Índice
     *
     * @return JsonResponse
     */
    public function indexAction()
    {
        $data = array(
            'msg'   => 'Index',
        );

        return new JsonResponse($data, 200);
    }

    /**
     * Ação Tabelas
     *
     * @return JsonResponse
     */
    public function tabelasAction()
    {
        return new JsonResponse($this->crawler->extractTabelas(), 200);
    }

    /**
     * Ação Extrai Marcas
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     *
     * @return JsonResponse
     */
    public function extractMarcasAction($tabelaId, $tipo)
    {
        return new JsonResponse($this->crawler->extractMarcas($tabelaId, $tipo), 200);
    }

    /**
     * Ação Extrai Modelos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     *
     * @return JsonResponse
     */
    public function extractModelosAction($tabelaId, $tipo, $marcaId)
    {
        return new JsonResponse($this->crawler->extractModelos($tabelaId, $tipo, $marcaId), 200);
    }

    /**
     * Ação Extrai Veículos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     * @param integer $modeloId Modelo Id
     *
     * @return JsonResponse
     */
    public function extractVeiculosAction($tabelaId, $tipo, $marcaId, $modeloId)
    {
        $tmpVeiculos = $this->crawler->extractVeiculos($tabelaId, $tipo, $marcaId, $modeloId, true);
        $this->db->saveVeiculoCompletos($tmpVeiculos);

        return new JsonResponse($tmpVeiculos, 200);
    }

    /**
     * Ação Extrai Modelos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     *
     * @return JsonResponse
     */
    public function extractModelosVeiculosAction($tabelaId, $tipo, $marcaId)
    {
        $modelos = $this->crawler->extractModelos($tabelaId, $tipo, $marcaId);
        $veiculosTotal = 0;
        foreach ($modelos['results'] as $key => $modelo) {
            $tmpVeiculos = $this->crawler->extractVeiculos($tabelaId, $tipo, $marcaId, $modelo, true);
            $this->db->saveVeiculoCompletos($tmpVeiculos);
            $veiculosTotal += count($tmpVeiculos);
        }
        $modelos['veiculosTotal'] = $veiculosTotal;

        return new JsonResponse($modelos, 200);
    }

    /**
     * Ação CSV Tabelas
     *
     * @return JsonResponse
     */
    public function csvTabelasAction()
    {
        return new JsonResponse($this->db->findTabelas(), 200);
    }

    /**
     * Ação CSV Veículos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     *
     * @return JsonResponse
     */
    public function csvVeiculosAction($tabelaId, $tipo)
    {
        return new JsonResponse($this->db->findVeiculosByTabelaAndTipo($tabelaId, $tipo), 200);
    }

    /**
     * Erro 404
     *
     * @return JsonResponse
     */
    public function error404Action()
    {
        $data = array(
            'error' => true,
            'msg'   => 'URL não encontrada: '.$this->request->getUri(),
        );

        return new JsonResponse($data, 404);
    }
}
