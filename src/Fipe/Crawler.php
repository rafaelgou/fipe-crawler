<?php

namespace Fipe;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\Stopwatch\Stopwatch;

class Crawler {

    static $urls = array(
        'tabelas'    => 'http://www.fipe.org.br/pt-br/indices/veiculos',
        'marcas'     => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarMarcas',
        'modelos'    => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarModelos',
        'anoModelos' => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarAnoModelo',
        'veiculo'    => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarValorComTodosParametros',
    );

    static $tipoVeiculos = array(
        1 => 'carro',
        2 => 'moto',
        3 => 'caminhao'
    );

    static $tipoVeiculosFull = array(
        1 => 'Carro',
        2 => 'Moto',
        3 => 'Caminhão',
        //999 => 'Todos',
    );

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch|null
     */
    protected $stopwatch = null;

    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    public function getTabelas()
    {
        $crawler = new DomCrawler();
        $html = file_get_contents(self::$urls['tabelas']);
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $crawler->addContent($html);

        $tabelas = array();
        $options = $crawler->filter('select#selectTabelaReferenciacarro')->children();
        foreach($options as $option) {
            $tabelas[$option->getAttribute('value')] = $option->nodeValue;
        }

        return $tabelas;
    }

    public function getTabelaByAnoMes($ano, $mes)
    {
        $tabelas = $this->extractTabelas();
        foreach ($tabelas['results'] as $tabela) {
            if (!array_key_exists('ano', $tabela)) {
                print_r($tabela);
            }
            $comparar = $tabela['ano'] . $tabela['mes'];
            if ($comparar === $ano . $mes) {
                return $tabela;
            }
        }

        return null;
    }

    public function getMarcas($tabela, $tipoVeiculo)
    {
        $params = array(
            'codigoTabelaReferencia' => $tabela,
            'codigoTipoVeiculo'      => $tipoVeiculo,
        );
        $url     = self::$urls['marcas'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach($tmp as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    public function getModelos($tabela, $tipoVeiculo, $marca)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipoVeiculo,
            'codigoTabelaReferencia' => $tabela,
            'codigoModelo'           => '',
            'codigoMarca'            => $marca,
            'ano'                    => '',
            'codigoTipoCombustivel'  => '',
            'anoModelo'              => '',
            'modeloCodigoExterno'    => '',
        );
        $url     = self::$urls['modelos'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach($tmp->Modelos as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    public function getAnoModelos($tabela, $tipoVeiculo, $marca, $modelo)
    {

        $params = array(
            'codigoTipoVeiculo'      => $tipoVeiculo,
            'codigoTabelaReferencia' => $tabela,
            'codigoModelo'           => $modelo,
            'codigoMarca'            => $marca,
            'ano'                    => '',
            'codigoTipoCombustivel'  => '',
            'anoModelo'              => '',
            'modeloCodigoExterno'    => '',
        );
        $url     = self::$urls['anoModelos'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach($tmp as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    public function getVeiculo($tabela, $tipo, $marca, $modelo, $combustivel, $ano)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipo,
            'codigoTabelaReferencia' => $tabela,
            'codigoModelo'           => $modelo,
            'codigoMarca'            => $marca,
            'codigoTipoCombustivel'  => $combustivel,
            'anoModelo'              => $ano,
            'modeloCodigoExterno'    => '',
            'tipoVeiculo'            => self::$tipoVeiculos[$tipo],
            'tipoConsulta'           => 'tradicional',
        );
        $url    = self::$urls['veiculo'];
        $record = json_decode($this->httpPost($url, $params));

        return get_object_vars($record);
    }

    public function httpPost($url, $params)
    {
        foreach($params as $k => $v)
        {
          $params[$k] = $k . '=' .$v;
        }
        $postData = implode('&', $params);
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output=curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    public function getVeiculos($tabela, $tipoVeiculo)
    {
        $veiculos = array();

        $marcas = $this->getMarcas($tabela, $tipoVeiculo);
        foreach ($marcas as $marca) {
            $modelos = $this->getModelos($tabela, $tipoVeiculo, $marca->Value);
            foreach ($modelos as $modelo) {
                $anoModelos = $this->getAnoModelos(
                    $tabela, $tipoVeiculo, $marca->Value, $modelo->Value
                );

                foreach ($anoModelos as $anoModelo) {
                    $tmpValue    = explode('-', $anoModelo->Value);
                    $ano         = $tmpValue[0];
                    $combustivel = $tmpValue[1];
                    $veiculos[] = $this->getVeiculo(
                        $tabela, $tipoVeiculo, $marca->Value,
                        $modelo->Value, $combustivel, $ano
                    );
                }
            }
        }

        return $veiculos;
    }

    public function extractTabelas()
    {
        $this->stopwatch->start('progress');
        $tabelas = $this->getTabelas();
        $results = array();
        foreach ($tabelas as $id => $tabela) {

            $tmp = explode('/', $tabela);
            $results[] = array(
                'id'  => $id,
                'lbl' => $tabela,
                'ano' => trim($tmp[1]),
                'mes' => trim(Database::$meses[$tmp[0]]),
            );
        }
        $event = $this->stopwatch->stop('progress');
        $data = array(
            'results'  => $results,
            'duration' => $event->getDuration(),
            'memory'   => $event->getMemory(),
        );

        return $data;
    }

    public function extractMarcas ($tabela, $tipo)
    {
        $this->stopwatch->start('progress');
        $marcas  = $this->getMarcas($tabela, $tipo);
        $results = array();
        foreach ($marcas as $id => $marca) {
            $results[] = array(
                'id'      => $id,
                'lbl'     => $marca,
                'tipo'    => $tipo,
                'modelos' => array(),
                'status'  => false,
            );
        }
        $event = $this->stopwatch->stop('progress');
        $data = array(
            'results'  => $results,
            'duration' => $event->getDuration(),
            'memory'   => $event->getMemory(),
        );

        return $data;
    }

    public function extractModelos ($tabela, $tipo, $marca)
    {
        $this->stopwatch->start('progress');
        $modelos = $this->getModelos($tabela, $tipo, $marca);
        $results = array();
        foreach ($modelos as $id => $modelo) {
            $results[] = array(
                'id'         => $id,
                'lbl'        => $modelo,
                'tipo'       => $tipo,
                'anoModelos' => array(),
            );
        }
        $event = $this->stopwatch->stop('progress');
        $data = array(
            'results'  => $results,
            'duration' => $event->getDuration(),
            'memory'   => $event->getMemory(),
        );

        return $data;
    }

    public function extractVeiculos ($tabela, $tipo, $marca, $modelo, $getResult = false)
    {
        $this->stopwatch->start('progress');
        $results = array();
        $anoModelos = $this->getAnoModelos(
            $tabela, $tipo, $marca, $modelo
        );
        foreach ($anoModelos as $id => $anoModelo) {
            $tmpValue = explode('-', $id);
            $ano      = $tmpValue[0];
            $comb     = $tmpValue[1];
            $tmpResult['anoModelos'][] = array(
                'id'   => $id,
                'lbl'  => $anoModelo,
                'comb' => $comb,
                'ano'  => $ano,
            );
            $veiculo = $this->getVeiculo(
                $tabela, $tipo, $marca, $modelo, $comb, $ano
            );
            $valor = $veiculo['Valor'];
            $valor = str_replace('R$ ', '', $valor);
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
            $valor = (int) $valor;

            $tmpMes = explode(' ', $veiculo['MesReferencia']);
            $mesref = Database::$meses[$tmpMes[0]];
            $anoref = trim($tmpMes[2]);

            $results[] = array(
                'tabela_id'  => $tabela,
                'anoref'     => $anoref,
                'mesref'     => $mesref,
                'tipo'       => $tipo,
                'fipe_cod'   => trim($veiculo['CodigoFipe']),
                'marca_id'   => $marca,
                'marca'      => trim($veiculo['Marca']),
                'modelo_id'  => $modelo,
                'modelo'     => trim($veiculo['Modelo']),
                'anomod'     => trim($veiculo['AnoModelo']),
                'comb_cod'   => $comb,
                'comb_sigla' => trim($veiculo['SiglaCombustivel']),
                'comb'       => Database::$combustiveis[$comb],
                'valor'      => $valor,
            );
        }
        $event = $this->stopwatch->stop('progress');
        $data = array(
            'anoModResults' => $anoModelos,
            'veiculosTotal' => count($results),
            'duration'      => $event->getDuration(),
            'memory'        => $event->getMemory(),
        );

        if ($getResult) {
            $data['results'] = $results;
        }

        return $data;
    }


}
