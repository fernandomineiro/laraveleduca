ALTER TABLE cursos_turmas_inscricao
  add column assinatura tinyint(4) after status; -- assinatura para verificar se a inscricao faz parte de alguma assinatura

alter table cursos_turmas_inscricao
  add column fk_curso int(11) after fk_turma; -- fk_curso para facilitar rastreaamento da matricula do usuario

create table assinatura_conteudos(
  id INT AUTO_INCREMENT,
  fk_curso int NOT NULL,
  fk_assinatura int NOT NULL,
  assinatura tinyint(4),
  PRIMARY KEY (id)
);

ALTER TABLE assinatura_cursos
    add column assinatura tinyint(4) after fk_assinatura;

create table assinatura_trilhas(
   id INT AUTO_INCREMENT,
   fk_trilha int NOT NULL,
   fk_assinatura int NOT NULL,
   status tinyint(4),
   PRIMARY KEY (id)
);

create table usuario_assinatura(
   id INT AUTO_INCREMENT,
   fk_usuario int NOT NULL,
   fk_assinatura int NOT NULL,
   status tinyint(4),
   PRIMARY KEY (id)
);

ALTER TABLE assinatura DROP fk_trilha;

ALTER TABLE assinatura
    add column fk_faculdade int(11) after fk_usuario;

update usuarios_modulos set descricao = 'Módulos de Assinaturas' where id = 42;
update usuarios_modulos set descricao = 'Tipos de Assinatura' where id = 43;
update usuarios_modulos set fk_menu_id = 1 where id = 41;


INSERT INTO `dev_educaz`.`usuarios_modulos_acoes`
(`fk_criador_id`,
 `fk_atualizador_id`,
 `criacao`,
 `atualizacao`,
 `status`,
 `descricao`,
 `fk_elemento_id`)
VALUES
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', 'configurar_cursos', '9'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', 'altera_status_trilha', '9'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', 'altera_status_curso', '9');

INSERT INTO `dev_educaz`.`usuarios_modulos_x_acoes`
(`fk_criador_id`,
 `fk_atualizador_id`,
 `criacao`,
 `atualizacao`,
 `status`,
 `fk_modulo_id`,
 `fk_acao_id`,
 `parametro`,
 `sufixo_acao`,
 `middleware`,
 `tipo_rota`)
VALUES
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '42', '26', '1', '1', NULL, 'NULL'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '42', '27', '1', '1', NULL, NULL),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '42', '28', '1', '1', NULL, NULL),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '42', '29', '1', '1', NULL, 'POST');

INSERT INTO `dev_educaz`.`usuarios_perfil_x_modulos_acoes`
(
    `fk_criador_id`,
    `fk_atualizador_id`,
    `criacao`,
    `atualizacao`,
    `status`,
    `fk_modulo_acoes_id`,
    `fk_perfil_id`)
VALUES
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '642', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '643', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '644', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '645', '2');


update usuarios_modulos_acoes set descricao = 'salvar_cursos' where id = 29;
update usuarios_modulos_x_acoes set tipo_rota = 'GET' WHERE id = 642;
update usuarios_modulos_x_acoes set tipo_rota = 'GET' WHERE id = 643;
update usuarios_modulos_x_acoes set tipo_rota = 'GET' WHERE id = 644;


INSERT INTO `dev_educaz`.`usuarios_modulos`
(`id`,
`fk_criador_id`,
`fk_atualizador_id`,
`criacao`,
`atualizacao`,
`status`,
`descricao`,
`route_name`,
`route_uri`,
`view_caminho`,
`controller`,
`fk_menu_id`)
VALUES
(77, '1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', 'Assinatura Conteudo', 'assinatura_conteudo', 'assinatura_conteudo',
'assinatura_conteudo', 'AssinaturaConteudoController', '-1');

INSERT INTO `dev_educaz`.`usuarios_modulos_x_acoes`
(`fk_criador_id`,
`fk_atualizador_id`,
`criacao`,
`atualizacao`,
`status`,
`fk_modulo_id`,
`fk_acao_id`,
`parametro`,
`sufixo_acao`,
`middleware`,
`tipo_rota`)
VALUES
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '1', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '2', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '3', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '4', NULL, NULL, '1', 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '5', NULL, NULL, '1', 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '6', '1', '1', NULL, 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '7', NULL, NULL, NULL, 'POST'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '8', '1', '1', '1', 'PATCH'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '77', '9', '1', NULL, '1', 'DELETE');

INSERT INTO `dev_educaz`.`usuarios_perfil_x_modulos_acoes`
(
    `fk_criador_id`,
    `fk_atualizador_id`,
    `criacao`,
    `atualizacao`,
    `status`,
    `fk_modulo_acoes_id`,
    `fk_perfil_id`)
VALUES
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '673', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '674', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '675', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '676', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '677', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '678', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '679', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '680', '2'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '681', '2')

alter table assinatura
  add column fk_faculdade int(11) after fk_certificado;
