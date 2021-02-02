#inserindo ação de exportar para cursos e eventos
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
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '10', '39', NULL, NULL, NULL, 'POST'),
('60', '60', '2019-01-01 00:00:00', '2019-01-01 00:00:00', '1', '17', '39', NULL, NULL, NULL, 'POST')
