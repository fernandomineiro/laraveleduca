ALTER TABLE table cursos_turmas_agenda
ADD data_inicio date after data

ALTER TABLE table cursos_turmas_agenda
ADD hora_inicio time after data_inicio

ALTER TABLE table cursos_turmas_agenda
ADD data_final date after hora_inicio

ALTER TABLE table cursos_turmas_agenda
ADD hora_final time after data_final
