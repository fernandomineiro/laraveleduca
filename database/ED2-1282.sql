CREATE TABLE `cupom_aluno_sem_registro` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_faculdade` INT(11),
  `ra` varchar (100),
  `cpf` varchar (20),
  `email` varchar (255) NOT NULL,
  `nome` varchar (200),
  `fk_cupom` INT(11) NOT NULL,
  `numero_usos` INT(11) NOT NULL,
  PRIMARY KEY (`id`));

