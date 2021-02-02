ALTER TABLE `cursos_faculdades` ADD COLUMN `curso_gratis` TINYINT(1) NULL DEFAULT 0 AFTER `indisponivel_venda`;
ALTER TABLE `trilhas_faculdades` ADD COLUMN `gratis` TINYINT(1) NULL DEFAULT 0 AFTER `fk_trilha`;
ALTER TABLE `assinatura_faculdades` ADD COLUMN `gratis` TINYINT(1) NULL DEFAULT 0 AFTER `fk_faculdade`;
