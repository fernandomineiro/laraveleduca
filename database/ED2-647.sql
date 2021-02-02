alter table cursos add column duracao_dias int(11) after curador_share;
alter table cursos add column disponibilidade_dias int(11) null after duracao_dias;
alter table cursos add column duracao_total int(11) null after disponibilidade_dias;
