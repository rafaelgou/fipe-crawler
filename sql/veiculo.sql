SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `veiculo`;
CREATE TABLE `veiculo_completo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fipe_cod` varchar(10) DEFAULT NULL,
  `tabela_id` int(11) NOT NULL,
  `anoref` smallint(4) NOT NULL,
  `mesref` smallint(2) NOT NULL,
  `tipo` tinyint(1) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `marca` varchar(30) DEFAULT NULL,
  `modelo_id` int(11) NOT NULL,
  `modelo` varchar(30) NOT NULL,
  `anomod` smallint(4) NOT NULL,
  `comb_cod` tinyint(1) NOT NULL,
  `comb_sigla` char(1) NOT NULL,
  `comb` varchar(10) NOT NULL,
  `valor` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fipe_cod_anomod` (`fipe_cod`,`anomod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
