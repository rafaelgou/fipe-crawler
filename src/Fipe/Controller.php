<?php

namespace Fipe;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Stopwatch\Stopwatch;

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
        return new JsonResponse($this->crawler->extractModelos ($tabela, $tipo, $marca), 200);
    }

    public function extractVeiculosAction ($tabela, $tipo, $marca, $modelo)
    {
        return new JsonResponse($this->crawler->extractVeiculos ($tabela, $tipo, $marca, $modelo), 200);
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
