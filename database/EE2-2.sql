alter table quiz_questao add column dissertativa tinyint(4);

alter table cursos_categoria add column ementa varchar (500), add column disciplina tinyint(4);

alter table estrutura_curricular add column fk_escola varchar (500);

alter table cursos_secao
    add column data_disponibilidade varchar(60),
    add column ementa varchar (500);
    
alter table cursos_modulos
    add column horario varchar(60),
    add column tipo_atividade varchar(200),
    add column endereco varchar(255),
    add column fk_quiz int(11),
    add column fk_trabalho int(11),
    add column possui_nota tinyint(4);

create table nota_atividade(
  id INT AUTO_INCREMENT,
  tipo_nota  varchar(255),
  nota varchar(255),
  fk_modulo int(11),
  fk_usuario int(11)
  PRIMARY KEY (id)
);

create table nota_materia(
  id INT AUTO_INCREMENT,
  tipo_nota  varchar(255),
  nota varchar(255),
  fk_materia int(11),
  fk_usuario int(11)
  PRIMARY KEY (id)
);

create table nota_disciplina(
  id INT AUTO_INCREMENT,
  tipo_nota  varchar(255),
  nota varchar(255),
  fk_disciplina int(11),
  fk_usuario int(11)
  PRIMARY KEY (id)
);

create table recados (
    id INT AUTO_INCREMENT,
    mensagem text,
    fk_professor int(11),
    fk_turma int(11),
    fk_escola int(11),
    fk_materia int(11),
    data_criacao timestamp NULL DEFAULT NULL,
    data_atualizacao timestamp NULL DEFAULT NULL
    PRIMARY KEY (id)
)
