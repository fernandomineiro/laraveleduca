/**
 *  Alterações na tabela de INSCRICAO
 */ 
ALTER TABLE inscricao ADD COLUMN fk_turma INT NOT NULL;

#DROP TABLE cursos_turmas;

CREATE TABLE  cursos_turmas (
    id INT NOT NULL AUTO_INCREMENT,
    fk_curso INT NOT NULL, 
    nome TEXT NOT NULL,
    descricao TEXT,
    status tinyint NOT NULL DEFAULT 1,
    criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizacao TIMESTAMP,
    fk_criador INT,
    fk_atualizador INT,
    PRIMARY KEY(id)
);

ALTER TABLE cursos_turmas ADD COLUMN data TEXT; /* ex: dias 15, 16 e 17 de janeiro de 2019, das 16h às 20h"*/

#DROP TABLE cursos_turmas_agenda;
CREATE TABLE  cursos_turmas_agenda (
    id INT NOT NULL AUTO_INCREMENT,
    fk_turma INT NOT NULL, 
    nome TEXT NOT NULL,
    descricao TEXT,
    data DATE,
    status tinyint NOT NULL DEFAULT 1,
    criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizacao TIMESTAMP,
    fk_criador INT,
    fk_atualizador INT,
    PRIMARY KEY(id)
);



#DROP TABLE cursos_turmas_agenda_presenca;
CREATE TABLE  cursos_turmas_agenda_presenca (
    id INT NOT NULL AUTO_INCREMENT,
    fk_agenda INT NOT NULL, 
    fk_usuario INT NOT NULL,
    presente BOOLEAN DEFAULT true,
    status tinyint NOT NULL DEFAULT 1,
    criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizacao TIMESTAMP,
    fk_criador INT,
    fk_atualizador INT,
    PRIMARY KEY(id)
);

DROP TABLE cursos_turmas_incricao

CREATE TABLE cursos_turmas_inscricao (
  id int(11) NOT NULL AUTO_INCREMENT,
  fk_usuario int(11) NOT NULL,
  fk_turma int(11) NOT NULL,
  percentual_completo int(11) NOT NULL DEFAULT 0,
  data_criacao timestamp NULL DEFAULT NULL,
  data_atualizacao timestamp NULL DEFAULT NULL,
  criacao timestamp NULL DEFAULT NULL,
  atualizacao timestamp NULL DEFAULT NULL,
  status tinyint(4) NOT NULL,
  PRIMARY KEY (id)
);

insert into cursos_turmas (fk_curso, nome) VALUES (1, 'Turma A');
insert into cursos_turmas_agenda (fk_turma, nome, data) VALUES (1, 'Primeiro dia', '2019-06-01'), (1, 'Segundo dia', '2019-06-02');
insert into cursos_turmas_inscricao(fk_usuario, fk_turma) VALUES (1, 1) , (2,1);
insert into cursos_turmas_agenda_presenca(fk_usuario, fk_agenda) VALUES (1,1);