create table cursos_faculdades(
  id INT AUTO_INCREMENT,
  fk_curso int NOT NULL,
  fk_faculdade int NOT NULL,
  duracao_dias int NOT NULL,
  disponibilidade_dias int NOT NULL,
  PRIMARY KEY (id)
);


ALTER TABLE cursos DROP fk_faculdade;
ALTER TABLE cursos DROP duracao_dias;
ALTER TABLE cursos DROP disponibilidade_dias;
