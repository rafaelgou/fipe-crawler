<?php

namespace Fipe;

class Database {

    protected $conn = null;

    static $meses = array(
        'janeiro'   => 1,
        'fevereiro' => 2,
        'março'     => 3,
        'abril'     => 4,
        'maio'      => 5,
        'junho'     => 6,
        'julho'     => 7,
        'agosto'    => 8,
        'setembro'  => 9,
        'outubro'   => 10,
        'novembro'  => 11,
        'dezembro'  => 12,
    );

    static $combustiveis = array(
        1 => 'Gasolina',
        2 => 'Álcool',
        3 => 'Diesel',
        4 => 'Flex',
    );

    public function __construct($host, $dbname, $user, $pass)
    {
        $dsn = "mysql:dbname={$dbname};host={$host}";
        try {
            $this->conn = new \PDO($dsn, $user, $pass);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function atualizaPeriodos (array $tabelas)
    {
        $sql = "REPLACE INTO periodo (id, periodo, ano, mes) "
             . "VALUES (:id, :periodo, :ano, :mes);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($tabelas as $id => $periodo) {
            $tmp = explode('/', $periodo);
            $ano = $tmp[1];
            $mes = self::$meses[$tmp[0]];
            $mesesFlip = array_flip(self::$meses);

            $stmt->execute(array(
                ':id'      => $id,
                ':periodo' => "{$mesesFlip[$mes]}/{$ano}",
                ':ano'     => $ano,
                ':mes'     => $mes,
            ));
        }
    }

    public function atualizaMarcas (array $marcas, $idPeriodo, $tipoVeiculo)
    {
        $sql   = "REPLACE INTO marca (id, marca, tipo_veiculo) "
               . "VALUES (:id, :marca, :tipo_veiculo);";
        $sqlPM = "REPLACE INTO periodo_marca (id_periodo, id_marca) "
               . "VALUES (:id_periodo, :id_marca);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmtPM = $this->conn->prepare($sqlPM, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($marcas as $id => $marca) {
            $stmt->execute(array(
                ':id'           => $id,
                ':marca'        => $marca,
                ':tipo_veiculo' => $tipoVeiculo
            ));
            $stmtPM->execute(array(
                ':id_marca'   => $id,
                ':id_periodo' => $idPeriodo,
            ));

        }
    }

    public function atualizaModelos (array $modelos, $idMarca)
    {
        $sql = "REPLACE INTO modelo (id_marca, fipe_cod, modelo) "
             . "VALUES (:id_marca, :fipe_cod, :modelo);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($modelos as $fipeCod => $modelo) {
            $stmt->execute(array(
                ':fipe_cod' => $fipeCod,
                ':id_marca' => $idMarca,
                ':modelo'   => $modelo,
            ));
        }
    }

    public function atualizaAnoModelos (array $anoModelos, $idModelo)
    {

        $sql = "REPLACE INTO anomodelo (id_modelo, anomodelo, cod_anomodelo, combustivel, cod_combustivel) "
             . "VALUES (:id_modelo, :anomodelo, :cod_anomodelo, :combustivel, :cod_combustivel);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($anoModelos as $anoMod => $label) {
            $tmp      = explode('-', $anoMod);
            $ano      = $tmp[0];
            $codComb  = $tmp[1];
            $comb     = array_key_exists($codComb, $combustiveis)
                      ? $combustiveis[$codComb]
                      : 0;
            $stmt->execute(array(
                ':id_modelo'       => $idModelo,
                ':anomodelo'       => $ano,
                ':cod_anomodelo'   => $anoMod,
                ':combustivel'     => $comb,
                ':cod_combustivel' => $codComb,
            ));
        }
    }

    public function atualizaVeiculo ()
    {

    }
}
