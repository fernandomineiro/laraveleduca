-- create table para adicionar categorias a trilha
create table cupom_trilhas(
   id INT AUTO_INCREMENT,
   fk_cupom int NOT NULL,
   fk_trilha int NOT NULL,
   fk_faculdade int,
   PRIMARY KEY (id)
);

create table cupom_cursos(
   id INT AUTO_INCREMENT,
   fk_curso int NOT NULL,
   fk_faculdade int,
   fk_cupom int NOT NULL,
   PRIMARY KEY (id)
);

create table cupom_alunos(
   id INT AUTO_INCREMENT,
   fk_aluno int NOT NULL,
   fk_faculdade int,
   fk_cupom int NOT NULL,
   PRIMARY KEY (id)
);

create table cupom_cursos_categorias(
   id INT AUTO_INCREMENT,
   fk_categoria int NOT NULL,
   fk_cupom int NOT NULL,
   PRIMARY KEY (id)
);
