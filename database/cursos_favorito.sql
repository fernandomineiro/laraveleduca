CREATE TABLE `cursos_favorito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fk_aluno` int(11) NOT NULL,
  `fk_curso` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);