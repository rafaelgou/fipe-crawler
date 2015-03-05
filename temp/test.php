<?php

$tipoVei = 1;
$tabela  = 175;

$params = array(
    'codigoTabelaReferencia' => $tabela,
    'codigoTipoVeiculo'      => $tipoVei,
);
$urlMarcas = 'http://www2.fipe.org.br/IndicesConsulta-ConsultarMarcas';
$marcas = json_decode(httpPost($urlMarcas, $params));

echo "Importando Tabela Referência {$tabela} para tipo veículo {$tipoVei}";
echo '===================================================================';

foreach ($marcas as $marca) {

    echo "Marca {$marca->Label}" . PHP_EOL;
    echo '-----------------------' . PHP_EOL;

    $params = array(
        'codigoTipoVeiculo'      => $tipoVei,
        'codigoTabelaReferencia' => $tabela,
        'codigoModelo'           => '',
        'codigoMarca'            => $marca->Value,
        'ano'                    => '',
        'codigoTipoCombustivel'  => '',
        'anoModelo'              => '',
        'modeloCodigoExterno'    => '',
    );
    $urlModelos = 'http://www2.fipe.org.br/IndicesConsulta-ConsultarModelos';
    $modelos = json_decode(httpPost($urlModelos, $params));

    foreach ($modelos->Modelos as $modelo) {

        echo "Modelo {$modelo->Label}" . PHP_EOL;
        echo '-----------------------' . PHP_EOL;

        $params = array(
            'codigoTipoVeiculo'      => $tipoVei,
            'codigoTabelaReferencia' => $tabela,
            'codigoModelo'           => $modelo->Value,
            'codigoMarca'            => $marca->Value,
            'ano'                    => '',
            'codigoTipoCombustivel'  => '',
            'anoModelo'              => '',
            'modeloCodigoExterno'    => '',
        );
        $urlAnoModelos = 'http://www2.fipe.org.br/IndicesConsulta-ConsultarAnoModelo';
        $anoModelos = json_decode(httpPost($urlAnoModelos, $params));

        foreach ($anoModelos as $anoModelo) {

            // echo "Ano Modelo {$anoModelo->Label}" . PHP_EOL;
            // echo '------------------------------' . PHP_EOL;

            $tmpValue    = explode('-', $anoModelo->Value);
            $ano         = $tmpValue[0];
            $combustivel = $tmpValue[1];

            $params = array(
                'codigoTipoVeiculo'      => $tipoVei,
                'codigoTabelaReferencia' => $tabela,
                'codigoModelo'           => $modelo->Value,
                'codigoMarca'            => $marca->Value,
                'codigoTipoCombustivel'  => $combustivel,
                'anoModelo'              => $ano,
                'modeloCodigoExterno'    => '',
                'tipoVeiculo'            => 'carro',
                'tipoConsulta'           => 'tradicional',

            );
            $urlVeiculo = 'http://www2.fipe.org.br/IndicesConsulta-ConsultarValorComTodosParametros';
            $veiculo = json_decode(httpPost($urlVeiculo, $params));
            echo "{$veiculo->Marca} {$veiculo->Modelo} - "
               . "{$veiculo->AnoModelo} - {$veiculo->Combustivel} - {$veiculo->Valor}" . PHP_EOL;

        }

    }

}

function httpPost($url,$params)
{
    $postData = '';
    foreach($params as $k => $v)
    {
      $postData .= $k . '='.$v.'&';
    }
    rtrim($postData, '&');

    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $output=curl_exec($ch);

    curl_close($ch);
    return $output;
}
