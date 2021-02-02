alter table cursos_faculdades add column indisponivel_venda tinyint;

create table avisar_novas_turmas(
   id INT AUTO_INCREMENT,
   fk_curso int NOT NULL,
   fk_faculdade int NOT NULL,
   nome_aluno varchar(255) NOT NULL,
   email_aluno varchar(255) NOT NULL,
   data_criacao datetime,
   data_atualizacao datetime,
   PRIMARY KEY (id)
);

alter table avisar_novas_turmas add column data_criacao datetime after email_aluno;
alter table avisar_novas_turmas add column data_atualizacao datetime after data_criacao;

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
('97', '1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', 'Cursos Vencidos', 'cursosvencidos', 'cursosvencidos', 'cursosvencidos', 'CursosVencidosController', '1');

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
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '1', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '2', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '3', NULL, NULL, NULL, NULL),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '4', NULL, NULL, '1', 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '5', NULL, NULL, '1', 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '6', '1', '1', NULL, 'GET'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '7', NULL, NULL, NULL, 'POST'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '39', NULL, NULL, NULL, 'POST'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '8', '1', '1', '1', 'PATCH'),
('1', '1', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '97', '9', '1', NULL, '1', 'DELETE');


