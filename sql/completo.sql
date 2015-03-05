SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `anomod`;
CREATE TABLE `anomod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelo_id` int(11) NOT NULL,
  `desc` varchar(30) NOT NULL,
  `ano` smallint(4) NOT NULL,
  `anomod_cod` varchar(30) NOT NULL,
  `comb` varchar(30) NOT NULL,
  `comb_cod` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `modelo_id` (`modelo_id`),
  CONSTRAINT `anomodelo_ibfk_1` FOREIGN KEY (`modelo_id`) REFERENCES `modelo` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `marca`;
CREATE TABLE `marca` (
  `id` int(11) NOT NULL,
  `desc` varchar(30) NOT NULL,
  `tipo` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `desc_tipo` (`desc`,`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `modelo`;
CREATE TABLE `modelo` (
  `id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `desc` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `marca_id` (`marca_id`),
  CONSTRAINT `modelo_ibfk_2` FOREIGN KEY (`marca_id`) REFERENCES `marca` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ref_tab_mar_mod_ano`;
CREATE TABLE `ref_tab_mar_mod_ano` (
  `tabela_id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `modelo_id` int(11) NOT NULL,
  `anomod_id` int(11) NOT NULL,
  UNIQUE KEY `id_tabela_id_marca_anomod_id` (`tabela_id`,`marca_id`,`anomod_id`),
  KEY `marca_id` (`marca_id`),
  KEY `tabela_id` (`tabela_id`),
  KEY `anomod_id` (`anomod_id`),
  KEY `modelo_id` (`modelo_id`),
  CONSTRAINT `ref_tab_mar_mod_ano_ibfk_4` FOREIGN KEY (`modelo_id`) REFERENCES `modelo` (`id`),
  CONSTRAINT `ref_tab_mar_mod_ano_ibfk_1` FOREIGN KEY (`tabela_id`) REFERENCES `tabela` (`id`),
  CONSTRAINT `ref_tab_mar_mod_ano_ibfk_2` FOREIGN KEY (`marca_id`) REFERENCES `marca` (`id`),
  CONSTRAINT `ref_tab_mar_mod_ano_ibfk_3` FOREIGN KEY (`anomod_id`) REFERENCES `anomod` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tabela`;
CREATE TABLE `tabela` (
  `id` int(11) NOT NULL,
  `ano` smallint(6) NOT NULL,
  `mes` tinyint(4) NOT NULL,
  `desc` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `veiculo`;
CREATE TABLE `veiculo` (
  `id` int(11) NOT NULL,
  `fipe_cod` varchar(10) DEFAULT NULL,
  `tabela_id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `anomod_id` int(11) NOT NULL,
  `tipo` tinyint(4) NOT NULL,
  `modelo` varchar(30) NOT NULL,
  `comb_cod` tinyint(1) NOT NULL,
  `comb_sigla` char(1) NOT NULL,
  `comb` varchar(10) NOT NULL,
  `valor` decimal(2,0) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tabela_id` (`tabela_id`),
  KEY `marca_id` (`marca_id`),
  KEY `anomod_id` (`anomod_id`),
  CONSTRAINT `veiculo_ibfk_1` FOREIGN KEY (`tabela_id`) REFERENCES `tabela` (`id`),
  CONSTRAINT `veiculo_ibfk_2` FOREIGN KEY (`marca_id`) REFERENCES `marca` (`id`),
  CONSTRAINT `veiculo_ibfk_3` FOREIGN KEY (`anomod_id`) REFERENCES `anomod` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
