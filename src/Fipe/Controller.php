<?php
/**
 * Fipe Crawler
 * @author Rafael Goulart <rafaelgou@gmail.com>
 */

namespace Fipe;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Classe Controller
 * @author Rafael Goulart <rafaelgou@gmail.com>
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

    public function indexAction ()
    {
        $data = array(
            'msg'   => 'Index'
        );
        return new JsonResponse($data, 200);
    }

    public function tabelasAction ()
    {
        return new JsonResponse($this->crawler->extractTabelas(), 200);
    }

    public function extractMarcasAction ($tabela, $tipo)
    {
        return new JsonResponse($this->crawler->extractMarcas($tabela, $tipo), 200);
    }

    public function extractModelosAction ($tabela, $tipo, $marca)
    {
        return new JsonResponse($this->crawler->extractModelos($tabela, $tipo, $marca), 200);
    }

    public function extractVeiculosAction ($tabela, $tipo, $marca, $modelo)
    {
        $tmpVeiculos = $this->crawler->extractVeiculos($tabela, $tipo, $marca, $modelo, true);
        $this->db->saveVeiculoCompletos($tmpVeiculos);
        return new JsonResponse($tmpVeiculos, 200);
    }

    public function extractModelosVeiculosAction ($tabela, $tipo, $marca)
    {
        $modelos = $this->crawler->extractModelos($tabela, $tipo, $marca);
        $veiculosTotal = 0;
        foreach ($modelos['results'] as $key => $modelo) {
            $tmpVeiculos = $this->crawler->extractVeiculos($tabela, $tipo, $marca, $modelo, true);
            $this->db->saveVeiculoCompletos($tmpVeiculos);
            $veiculosTotal += count($tmpVeiculos);
        }
        $modelos['veiculosTotal'] = $veiculosTotal;
        return new JsonResponse($modelos, 200);
    }

    public function csvTabelasAction ()
    {
        return new JsonResponse($this->db->findTabelas(), 200);
    }
    public function csvVeiculosAction ($tabela, $tipo)
    {
        return new JsonResponse($this->db->findVeiculosByTabelaAndTipo($tabela, $tipo), 200);
    }

    public function error404Action ()
    {
        $data = array(
            'error' => true,
            'msg'   => 'URL não encontrada: ' . $this->request->getUri()
        );
        return new JsonResponse($data, 404);
    }

}
