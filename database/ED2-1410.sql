ALTER TABLE `cursos_modulos`
ADD COLUMN `aula_ao_vivo` INT(1) NULL DEFAULT 0 COMMENT 'possiveis valores: 0 ou 1, para indicar se essa determinada aula será aula ao vivo ou não.' AFTER `ordem`,
ADD COLUMN `data_aula_ao_vivo` DATE NULL AFTER `aula_ao_vivo`,
ADD COLUMN `hora_aula_ao_vivo` TIME NULL AFTER `data_aula_ao_vivo`,
ADD COLUMN `link_aula_ao_vivo` varchar(200) NULL AFTER `hora_aula_ao_vivo`;
