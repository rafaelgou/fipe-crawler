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


    public function __construct($host, $dbname, $user, $pass)
    {
        $dsn = "mysql:dbname={$dbname};host={$host}";
        try {
            $this->conn = new \PDO($dsn, $user, $pass);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function atualizaTabelas (array $tabelas)
    {
        $sql = "REPLACE INTO periodo (id, periodo, ano, mes) "
             . "VALUES (:id, :periodo, :ano, :mes);";
        $stmt = $this->conn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        foreach($tabelas as $id => $periodo) {
            $tmp = explode('/', $periodo);
            $ano = $tmp[1];
            $mes = self::$meses[$tmp[0]];

            $stmt->execute(array(
                ':id'      => $id,
                ':periodo' => $periodo,
                ':ano'     => $ano,
                ':mes'     => $mes,
            ));
        }
    }

    public function atualizaVeiculo ()
    {

    }
}
