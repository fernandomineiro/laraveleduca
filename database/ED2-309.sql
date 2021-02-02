ALTER TABLE `dev_educaz`.`usuarios` 
DROP COLUMN `atualizacao`,
DROP COLUMN `criacao`,
DROP COLUMN `cpf`,
DROP COLUMN `rg`,
DROP COLUMN `celular`,
DROP COLUMN `telefone`,
DROP COLUMN `data_nascimento`;

ALTER TABLE `dev_educaz`.`alunos` 
CHANGE COLUMN `cnpjcpf` `cpf` VARCHAR(20) NULL DEFAULT NULL ;

ALTER TABLE `dev_educaz`.`alunos` 
DROP COLUMN `fk_curso_id`,
DROP COLUMN `foto`;

ALTER TABLE `dev_educaz`.`faculdades` 
CHANGE COLUMN `cnpjcpf` `cnpj` VARCHAR(20) NULL DEFAULT NULL ;

ALTER TABLE `dev_educaz`.`produtora` 
CHANGE COLUMN `cnpjcpf` `cnpj` VARCHAR(20) NULL DEFAULT NULL ;

ALTER TABLE `dev_educaz`.`curadores` 
CHANGE COLUMN `cnpjcpf` `cnpj` VARCHAR(20) NULL DEFAULT NULL ;