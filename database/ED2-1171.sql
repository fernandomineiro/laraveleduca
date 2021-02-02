ALTER TABLE `educaz20prod`.`cursos` 
ADD COLUMN `professor_responde_duvidas` INT(1) NULL DEFAULT '1' AFTER `disponibilidade_dias`;