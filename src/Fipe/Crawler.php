<?php

namespace Fipe;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler {

    static $urls = array(
        'tabelas'    => 'http://www2.fipe.org.br/pt-br/indices/veiculos',
        'marcas'     => 'http://www2.fipe.org.br/IndicesConsulta-ConsultarMarcas',
        'modelos'    => 'http://www2.fipe.org.br/IndicesConsulta-ConsultarModelos',
        'anoModelos' => 'http://www2.fipe.org.br/IndicesConsulta-ConsultarAnoModelo',
        'veiculo'    => 'http://www2.fipe.org.br/IndicesConsulta-ConsultarValorComTodosParametros',
    );

    static $tipoVeiculos = array(
        1 => 'carro',
        2 => 'moto',
        3 => 'caminhao'
    );

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

    public function getMarcas($tabela, $tipoVeiculo)
    {
        $params = array(
            'codigoTabelaReferencia' => $tabela,
            'codigoTipoVeiculo'      => $tipoVeiculo,
        );
        $url = self::$urls['marcas'];

        return json_decode($this->httpPost($url, $params));
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
        $url = self::$urls['modelos'];
        $modelos = json_decode($this->httpPost($url, $params));

        return $modelos->Modelos;
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
        $url = self::$urls['anoModelos'];

        return json_decode($this->httpPost($url, $params));

    }

    public function getVeiculo($tabela, $tipoVeiculo, $marca, $modelo, $combustivel, $ano)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipoVei,
            'codigoTabelaReferencia' => $tabela,
            'codigoModelo'           => $modelo,
            'codigoMarca'            => $marca,
            'codigoTipoCombustivel'  => $combustivel,
            'anoModelo'              => $ano,
            'modeloCodigoExterno'    => '',
            'tipoVeiculo'            => self::$tipoVeiculos[$tipoVeiculo],
            'tipoConsulta'           => 'tradicional',
        );
        $url = self::$urls['veiculo'];
        $veiculo = json_decode(httpPost($url, $params));
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

}
