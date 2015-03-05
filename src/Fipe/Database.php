<?php

namespace Fipe;

class Database {

    protected $conn = null;

    static $meses = array(
        'janeiro'   => '01',
        'fevereiro' => '02',
        'março'     => '03',
        'abril'     => '04',
        'maio'      => '05',
        'junho'     => '06',
        'julho'     => '07',
        'agosto'    => '08',
        'setembro'  => '09',
        'outubro'   => '10',
        'novembro'  => '11',
        'dezembro'  => '12',
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

    public function saveTabelas (array $tabelas)
    {
        $results = array();

        $sql = "INSERT INTO tabela (id, desc, ano, mes) "
             . "VALUES (:id, :desc, :ano, :mes);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($tabelas as $id => $desc) {
            $tmp = explode('/', $desc);
            $ano = $tmp[1];
            $mes = self::$meses[$tmp[0]];
            $mesesFlip = array_flip(self::$meses);

            $record = array(
                ':id'   => $id,
                ':desc' => "{$mesesFlip[$mes]}/{$ano}",
                ':ano'  => $ano,
                ':mes'  => $mes,
            );
            $stmt->execute($record);
            $results[] = $record;
        }

        return $results;
    }

    public function saveMarcas (array $marcas, $tipo)
    {
        $results = array();

        $sql = "INSERT INTO marca (id, desc, tipo) "
             . "VALUES (:id, :desc, :tipo);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($marcas as $id => $desc) {
            $record = array(
                ':id'   => $id,
                ':desc' => $desc,
                ':tipo' => $tipo
            );
            $stmt->execute($record);
            $record = $this->cleanRecord($record);
            $results[] = $record;
        }

        return $results;
    }

    public function saveModelos (array $modelos, $marcaId)
    {
        $results = array();

        $sql = "INSERT INTO modelo (id, marca_id, desc) "
             . "VALUES (:id, :marca_id, :desc);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($modelos as $id => $desc) {
            $record = array(
                ':id'       => $id,
                ':marca_id' => $marcaId,
                ':desc'     => $desc,
            );
            $stmt->execute($record);
            $record = $this->cleanRecord($record);
            $results[] = $record;
        }

        return $results;
    }

    public function saveAnoModelos (array $anoMods, $tabelaId, $marcaId, $modeloId)
    {
        $results = array();

        $sql = "INSERT INTO anomod (modelo_id, desc, anomod_cod, ano, comb, comb_cod) "
             . "VALUES (:modelo_id, :desc, :anomod_cod, :ano, :comb, :comb_cod);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));

        $sqlRef = "INSERT INTO ref_tab_mar_mod_ano (tabela_id, marca_id, modelo_id, anomod_id) "
            . "VALUES (:tabela_id, :marca_id, :modelo_id, :anomod_id);";
        $stmtRef = $this->conn->prepare($sqlRef, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));

        foreach($anoMods as $anoMod => $desc) {
            $tmp      = explode('-', $anoMod);
            $ano      = $tmp[0];
            $combCod  = $tmp[1];
            $comb     = array_key_exists($combCod, self::$combustiveis)
                      ? self::$combustiveis[$combCod]
                      : 0;
            $record = array(
                ':modelo_id'  => $modeloId,
                ':desc'       => $desc,
                ':anomod_cod' => $anoMod,
                ':ano'        => $ano,
                ':comb'       => $comb,
                ':comb_cod'   => $combCod,
            );
            $stmt->execute($record);
            $record = $this->cleanRecord($record);
            $record['id'] = $this->conn->lastInsertId();
            $results[] = $record;

            $stmtRef->execute(array(
                ':tabela_id' => $tabelaId,
                ':marca_id'  => $marcaId,
                ':modelo_id' => $modeloId,
                ':anomod_id' => $record['id'],
            ));

        }

        return $results;
    }

    public function saveVeiculos ($veiculos, $anoModId )
    {
        $results = array();

        $sql = "INSERT INTO veiculo (fipe_cod, tabela_id, marca_id, anomod_id, tipo, modelo, comb_cod, comb_sigla, comb, valor) "
            . "VALUES (:fipe_cod, :tabela_id, :marca_id, :anomod_id, :tipo, :modelo, :comb_cod, :comb_sigla, :comb, :valor);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($veiculos as $id => $veiculo) {
            $record = array(
                ':fipe_cod'   => $veiculo['fipe_cod'],
                ':tabela_id'  => $veiculo['tabela_id'],
                ':marca_id'   => $veiculo['marca_id'],
                ':anomod_id'  => $anoModId,
                ':tipo'       => $veiculo['tipo'],
                ':modelo'     => $veiculo['modelo'],
                ':comb_cod'   => $veiculo['comb_cod'],
                ':comb_sigla' => $veiculo['comb_sigla'],
                ':comb'       => $veiculo['comb'],
                ':valor'      => $veiculo['valor'],
            );
            $stmt->execute($record);

            $record = $this->cleanRecord($record);
            $record['id'] = $this->conn->lastInsertId();
            $results[]    = $record;
        }

        return $results;
    }

    public function saveVeiculoCompletos ($veiculos)
    {
        $results = array();

        $sql = "INSERT INTO veiculo_completo (fipe_cod, tabela_id, anoref, mesref, tipo, marca_id, marca, modelo_id, modelo, anomod, comb_cod, comb_sigla, comb, valor) "
            . "VALUES (:fipe_cod, :tabela_id, :anoref, :mesref, :tipo, :marca_id, :marca, :modelo_id, :modelo, :anomod, :comb_cod,:comb_sigla, :comb, :valor);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($veiculos['results'] as $veiculo) {
            try {
                $record = $this->prepareParameters($veiculo);
                $stmt->execute($record);
                $veiculo['id'] = $this->conn->lastInsertId();
                $results[]     = $veiculo;
            } catch (\Exception $e) {
                // Ignore
            }
        }
        return $results;

    }

    protected function cleanRecord($record)
    {
        foreach ($record as $id => $value) {
            $newId = substr($id, 1);
            $record[$newId] = $value;
            unset($record[$id]);
        }

        return $record;
    }

    protected function prepareParameters($record)
    {
        foreach ($record as $id => $value) {
            $newId = ":{$id}";
            $record[$newId] = $value;
            unset($record[$id]);
        }

        return $record;
    }

    public function findVeiculos($anoref, $mesref, $tipo)
    {
        $sql = "SELECT * FROM veiculo_completo "
             . " WHERE anoref = :anoref AND mesref = :mesref AND tipo = :tipo;";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));

        $stmt->execute(array(
            ':anoref' => (int) $anoref,
            ':mesref' => (int) $mesref,
            ':tipo'   => (int) $tipo,
        ));

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


//    public function findVeiculoByFipeAndAnomod($fipeCod, $anomod)
//    {
//
//    }
//
//SELECT * FROM veiculo_completo
//WHERE fipe_cod LIKE "%501014-4%"
//AND anomod = 1999

    public function getCsvHeader($row, $noId = false, $separator = ',')
    {
        if ($noId) {
            unset($row['id']);
        }

        return implode($separator, array_keys($row));
    }

    public function prepareCsvRow($row, $noId = false, $separator = ',')
    {
        if ($noId) {
            unset($row['id']);
        }

        return implode($separator, $row);
    }

}
