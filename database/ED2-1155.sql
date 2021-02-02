ALTER TABLE `usuarios_assinaturas` ADD COLUMN `renovacao_cancelada` TINYINT(1) NULL DEFAULT 0 AFTER `invoice_id_wirecard`;
ALTER TABLE `usuarios_assinaturas` ADD COLUMN `cancelamento_agendado` TIMESTAMP NULL AFTER `fk_criador_id`;

