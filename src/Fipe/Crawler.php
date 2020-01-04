<?php
/**
 * Fipe Crawler
 *
 * @category Crawler
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */

namespace Fipe;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Classe Crawler
 *
 * @category Crawler
 * @package  Fipe
 * @author   Rafael Goulart <rafaelgou@gmail.com>
 * @license  MIT <https://github.com/rafaelgou/fipe-crawler/LICENSE.md>
 * @link     https://github.com/rafaelgou/fipe-crawler
 */
class Crawler
{

    /**
     * @var array
     */
    public static $urls = array(
        'tabelas'    => 'http://www.fipe.org.br/pt-br/indices/veiculos',
        'marcas'     => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarMarcas',
        'modelos'    => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarModelos',
        'anoModelos' => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarAnoModelo',
        'veiculo'    => 'http://veiculos.fipe.org.br/api/veiculos/ConsultarValorComTodosParametros',
    );

    /**
     * @var array
     */
    public static $tipos = array(
        1 => 'carro',
        2 => 'moto',
        3 => 'caminhao',
    );

    /**
     * @var array
     */
    public static $tiposFull = array(
        1 => 'Carro',
        2 => 'Moto',
        3 => 'Caminhão',
        //999 => 'Todos',
    );

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch|null
     */
    protected $stopwatch = null;

    /**
     * Construtor
     *
     * @return void
     */
    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    /**
     * Recupera tabelas
     *
     * @return array
     */
    public function getTabelas()
    {
        $crawler = new DomCrawler();
        $html = file_get_contents(self::$urls['tabelas']);
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $crawler->addContent($html);

        $tabelas = array();
        $options = $crawler->filter('select#selectTabelaReferenciacarro')->children();
        foreach ($options as $option) {
            $tabelas[$option->getAttribute('value')] = $option->nodeValue;
        }

        return $tabelas;
    }

    /**
     * Recupera tabela por ano e mês
     *
     * @param string $ano Ano
     * @param string $mes Mês
     *
     * @return string|null
     */
    public function getTabelaByAnoMes($ano, $mes)
    {
        $tabelas = $this->extractTabelas();
        foreach ($tabelas['results'] as $tabela) {
            $comparar = $tabela['ano'].$tabela['mes'];
            if ($comparar === $ano.$mes) {
                return $tabela;
            }
        }

        return null;
    }

    /**
     * Recupera marcas
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     *
     * @return array
     */
    public function getMarcas($tabelaId, $tipo)
    {
        $params = array(
            'codigoTabelaReferencia' => $tabelaId,
            'codigoTipoVeiculo'      => $tipo,
        );
        $url     = self::$urls['marcas'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach ($tmp as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    /**
     * Recupera modelos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     *
     * @return array
     */
    public function getModelos($tabelaId, $tipo, $marcaId)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipo,
            'codigoTabelaReferencia' => $tabelaId,
            'codigoModelo'           => '',
            'codigoMarca'            => $marcaId,
            'ano'                    => '',
            'codigoTipoCombustivel'  => '',
            'anoModelo'              => '',
            'modeloCodigoExterno'    => '',
        );
        $url     = self::$urls['modelos'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach ($tmp->Modelos as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    /**
     * Recupera ano modelos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     * @param integer $modeloId Modelo Id
     *
     * @return array
     */
    public function getAnoModelos($tabelaId, $tipo, $marcaId, $modeloId)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipo,
            'codigoTabelaReferencia' => $tabelaId,
            'codigoModelo'           => $modeloId,
            'codigoMarca'            => $marcaId,
            'ano'                    => '',
            'codigoTipoCombustivel'  => '',
            'anoModelo'              => '',
            'modeloCodigoExterno'    => '',
        );
        $url     = self::$urls['anoModelos'];
        $tmp     = json_decode($this->httpPost($url, $params));
        $records = array();
        foreach ($tmp as $t) {
            $records[$t->Value] = $t->Label;
        }

        return $records;
    }

    /**
     * Recupera veículo
     *
     * @param integer $tabelaId    Tabela Id
     * @param integer $tipo        Tipo
     * @param integer $marcaId     Marca Id
     * @param integer $modeloId    Modelo Id
     * @param string  $combustivel Código combustível
     * @param intege  $ano         Ano Modelo
     *
     * @return array
     */
    public function getVeiculo($tabelaId, $tipo, $marcaId, $modeloId, $combustivel, $ano)
    {
        $params = array(
            'codigoTipoVeiculo'      => $tipo,
            'codigoTabelaReferencia' => $tabelaId,
            'codigoModelo'           => $modeloId,
            'codigoMarca'            => $marcaId,
            'codigoTipoCombustivel'  => $combustivel,
            'anoModelo'              => $ano,
            'modeloCodigoExterno'    => '',
            'tipoVeiculo'            => self::$tipos[$tipo],
            'tipoConsulta'           => 'tradicional',
        );
        $url    = self::$urls['veiculo'];
        $record = json_decode($this->httpPost($url, $params));

        return get_object_vars($record);
    }

    /**
     * Extrai veículos
     *
     * @param string $url    URL para request
     * @param array  $params Parâmetros
     *
     * @return string
     */
    public function httpPost($url, $params)
    {
        foreach ($params as $k => $v) {
            $params[$k] = $k.'='.$v;
        }
        $postData = implode('&', $params);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept:application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding:gzip, deflate',
                'Accept-Language:pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4',
                'Connection:keep-alive',
                'Content-Type:application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie:_ga=GA1.3.472052299.1466616166; _gat=1',
                'Host:veiculos.fipe.org.br',
                'Origin:http://veiculos.fipe.org.br',
                'Referer:http://veiculos.fipe.org.br/',
                'User-Agent:Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
                'X-Requested-With:XMLHttpRequest',
        ));

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Recupera veículos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     *
     * @return array
     */
    public function getVeiculos($tabelaId, $tipo)
    {
        $veiculos = array();

        $marcas = $this->getMarcas($tabelaId, $tipo);
        foreach ($marcas as $marca) {
            $modelos = $this->getModelos($tabelaId, $tipo, $marca->Value);
            foreach ($modelos as $modelo) {
                $anoModelos = $this->getAnoModelos(
                    $tabelaId,
                    $tipo,
                    $marca->Value,
                    $modelo->Value
                );

                foreach ($anoModelos as $anoModelo) {
                    $tmpValue    = explode('-', $anoModelo->Value);
                    $ano         = $tmpValue[0];
                    $combustivel = $tmpValue[1];
                    $veiculos[] = $this->getVeiculo(
                        $tabelaId,
                        $tipo,
                        $marca->Value,
                        $modelo->Value,
                        $combustivel,
                        $ano
                    );
                }
            }
        }

        return $veiculos;
    }

    /**
     * Extrai tabelas
     *
     * @return array
     */
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

    /**
     * Extrai marcas
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     *
     * @return array
     */
    public function extractMarcas($tabelaId, $tipo)
    {
        $this->stopwatch->start('progress');
        $marcas  = $this->getMarcas($tabelaId, $tipo);
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

    /**
     * Extrai modelos
     *
     * @param integer $tabelaId Tabela Id
     * @param integer $tipo     Tipo
     * @param integer $marcaId  Marca Id
     *
     * @return array
     */
    public function extractModelos($tabelaId, $tipo, $marcaId)
    {
        $this->stopwatch->start('progress');
        $modelos = $this->getModelos($tabelaId, $tipo, $marcaId);
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

    /**
     * Extrai veículos
     *
     * @param integer $tabelaId  Tabela Id
     * @param integer $tipo      Tipo
     * @param integer $marcaId   Marca Id
     * @param integer $modeloId  Modelo Id
     * @param boolean $getResult Recuperar ou não resultados
     *
     * @return array
     */
    public function extractVeiculos($tabelaId, $tipo, $marcaId, $modeloId, $getResult = false)
    {
        $this->stopwatch->start('progress');
        $results = array();
        $anoModelos = $this->getAnoModelos(
            $tabelaId,
            $tipo,
            $marcaId,
            $modeloId
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
                $tabelaId,
                $tipo,
                $marcaId,
                $modeloId,
                $comb,
                $ano
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
                'tabela_id'  => $tabelaId,
                'anoref'     => $anoref,
                'mesref'     => $mesref,
                'tipo'       => $tipo,
                'fipe_cod'   => trim($veiculo['CodigoFipe']),
                'marca_id'   => $marcaId,
                'marca'      => trim($veiculo['Marca']),
                'modelo_id'  => $modeloId,
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
