CREATE TABLE `cursos_concluidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_faculdade` INT(11) NOT NULL,
  `fk_usuario` INT(11) NOT NULL,
  `fk_curso` INT(11) NULL,
  `nota_trabalho` INT(11) NULL DEFAULT NULL,
  `nota_quiz` INT(11) NULL DEFAULT NULL,
  `frequencia` INT(11) NULL DEFAULT NULL,
  `criacao` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`));

