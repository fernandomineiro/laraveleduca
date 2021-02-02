ALTER TABLE `dev_educaz`.`pedidos_item_split` 
ADD COLUMN `valor_split_professor` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_professor`,
ADD COLUMN `valor_split_professor_participante` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_professor_participante`,
ADD COLUMN `valor_split_curador` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_curador`,
ADD COLUMN `valor_split_parceiro` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_parceiro`,
ADD COLUMN `valor_split_faculdade` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_faculdade`,
ADD COLUMN `valor_split_produtora` DECIMAL(10,2) NULL DEFAULT NULL AFTER `porcentagem_split_produtora`;
