-- create table para adicionar categorias a trilha
create table trilhas_categoria(
   id INT AUTO_INCREMENT,
   fk_categoria int NOT NULL,
   fk_trilha int NOT NULL,
   PRIMARY KEY (id)
);

-- create table para adicionar faculdades a trilha
create table trilhas_faculdades(
   id INT AUTO_INCREMENT,
   fk_faculdade int NOT NULL,
   fk_trilha int NOT NULL,
   PRIMARY KEY (id)
);

create table trilha_quiz(
   id INT AUTO_INCREMENT,
   fk_trilha int NOT NULL,
   percentual_acerto int,
   status int not null,
   tipoquest varchar(10) not null,
   fk_atualizador_id int not null,
   fk_criador_id int not null,
   criacao timestamp not null,
   atualizacao timestamp not null,
   PRIMARY KEY (id)
);
create table trilha_quiz_questao(
   id INT AUTO_INCREMENT,
   fk_trilha_quiz int NOT NULL,
   titulo varchar (500) not null,
   resposta_correta int(11),
   status int not null,
   fk_atualizador_id int not null,
   fk_criador_id int not null,
   criacao timestamp not null,
   atualizacao timestamp not null,
   PRIMARY KEY (id)
);

create table trilha_quiz_resposta(
   id INT AUTO_INCREMENT,
   fk_trilha_quiz_questao int NOT NULL,
   descricao varchar (500) not null,
   status int not null,
   fk_atualizador_id int not null,
   fk_criador_id int not null,
   criacao timestamp not null,
   atualizacao timestamp not null,
   PRIMARY KEY (id)
);

alter table trilha add column duracao_total decimal (12,2) after fk_certificado;
alter table trilha add column teaser varchar(1000) after fk_certificado;
alter table trilha add column imagem varchar(255) after fk_certificado;
alter table trilha add column fk_professor integer (11) after fk_certificado;
alter table trilha add column fk_curador integer (11) after fk_certificado;
alter table trilha add column fk_produtora integer (11) after fk_certificado;
alter table trilha add column questionario varchar(11) after fk_certificado;
