ALTER TABLE `pedidos_item_split` 
ADD COLUMN `impostos_taxas_split_professor` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_professor`,
ADD COLUMN `impostos_taxas_split_professor_participante` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_professor_participante`,
ADD COLUMN `impostos_taxas_split_curador` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_curador`,
ADD COLUMN `impostos_taxas_split_parceiro` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_parceiro`,
ADD COLUMN `impostos_taxas_split_faculdade` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_faculdade`,
ADD COLUMN `impostos_taxas_split_produtora` DECIMAL(10,2) NULL DEFAULT NULL AFTER `valor_split_produtora`;
