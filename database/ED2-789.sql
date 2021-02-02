CREATE TABLE `usuarios_assinaturas_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mes` int(2) DEFAULT NULL,
  `ano` int(4) DEFAULT NULL,
  `total` int(6) DEFAULT NULL,
  `tipo` varchar(45) DEFAULT NULL COMMENT 'Ex.: ''ATIVOS'': Totalizacao de assinantes ativos naquele momento.  ',
  `criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

# RELATORIO DE REPASSES
ALTER TABLE `assinatura_pagamento` CHANGE COLUMN `pagamento_wirecard_id` `pagamento_wirecard_id` VARCHAR(45) NOT NULL;
ALTER TABLE `assinatura_pagamento` DROP COLUMN `total`;

CREATE TABLE `assinatura_repasse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_faculdade` int(11) DEFAULT NULL,
  `fk_assinatura` int(11) DEFAULT NULL,
  `total_arrecadado` decimal(12,2) DEFAULT NULL,
  `valor_view` decimal(12,2) DEFAULT NULL,
  `total_views` int(11) DEFAULT NULL,
  `total_parceiros` int(11) DEFAULT NULL,
  `total_assinantes` int(11) DEFAULT NULL,
  `mes` varchar(2) DEFAULT NULL,
  `ano` varchar(4) DEFAULT NULL,
  `atualizacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Resumo de repasses para produtos do tipo assinatura mês a mês';

CREATE TABLE `assinatura_repasse_parceiro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_assinatura_repasse` int(11) DEFAULT NULL,
  `fk_usuario` varchar(45) DEFAULT NULL,
  `fk_curso` int(11) DEFAULT NULL,
  `tipo_usuario` varchar(45) DEFAULT NULL COMMENT 'professor, curador, produtora, faculdade',
  `total_views` int(11) DEFAULT NULL,
  `percentual_repasse` decimal(5,2) DEFAULT NULL,
  `atualizacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

