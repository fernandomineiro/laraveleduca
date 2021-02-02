CREATE TABLE `cursos_concluidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_faculdade` int(11) NOT NULL,
  `fk_usuario` int(11) NOT NULL,
  `fk_curso` int(11) DEFAULT NULL,
  `nota_trabalho` int(11) DEFAULT NULL,
  `nota_quiz` int(11) DEFAULT NULL,
  `carga_horaria` int(11) DEFAULT NULL,
  `frequencia` int(11) DEFAULT NULL,
  `criacao` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;
