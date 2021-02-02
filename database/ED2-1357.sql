ALTER TABLE `cursos_categoria` 
ADD COLUMN `slug_categoria` VARCHAR(255) NULL COMMENT 'será utilizado no front-end para montar URL amigável' AFTER `titulo`;

ALTER TABLE `trilha` 
ADD COLUMN `slug_trilha` VARCHAR(255) NULL COMMENT 'será utilizado no front-end para montar URL amigável' AFTER `atualizacao`;

ALTER TABLE `cursos` 
ADD COLUMN `slug_curso` VARCHAR(255) NULL COMMENT 'utilizado para compor a url amigável, para ajudar nas questões de SEO. Criado por Gabriel Carvalho dia 07/03/2020 20:43 hs' AFTER `titulo`;