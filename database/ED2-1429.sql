create table cursos_mentoria_comentarios
(
    id               int auto_increment
        primary key,
	avaliacao int default 0 null,
	comentario text null,
	fk_professor int null,
	fk_curso int null,
	fk_criador_id int null,
	data_criacao timestamp default current_timestamp() null,
	data_atualizacao timestamp default current_timestamp() null
);

INSERT INTO cursos_tipo (id, titulo, fk_criador_id, fk_atualizador_id, data_criacao, data_atualizacao, criacao, atualizacao, status) VALUES (5, 'Mentoria', 1, 64, null, null, '2020-04-06 21:50:39', '2020-04-06 21:50:41', 1)

alter table configuracoes_tipos_cursos_ativos
	add ativar_cursos_mentoria int default 1 null after ativar_cursos_hibridos;

INSERT INTO educaz20prod.tipo_assinatura (titulo, fk_criador_id, fk_atualizador_id, data_criacao, data_atualizacao, criacao, atualizacao, status) VALUES ('Mentoria', 1, 1, null, null, '2020-04-21 14:02:12', '2020-04-21 14:02:14', 1)
