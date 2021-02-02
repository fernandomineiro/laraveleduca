alter table conta_bancaria add column tipo_conta varchar(11) null after operacao;
alter table conta_bancaria add column digita_conta int(11) null after conta_corrente;
alter table conta_bancaria add column digita_agencia int(11) null after agencia;


alter table curadores add column titular_curador varchar(255) after representante_legal;
alter table professor add column share decimal (14,2) after profissao;

CREATE VIEW `vw_usuarios_curadores` AS
    SELECT
        `curadores`.`id` AS `curador_id`,
        `curadores`.`data_criacao` AS `registro`,
        `curadores`.`titular_curador` AS `nome`,
        `curadores`.`cnpj` AS `cnpj`,
        `curadores`.`cpf` AS `cpf`,
        `curadores`.`status` AS `registro_ativa`,
        `usuarios`.`email` AS `email`,
        `usuarios`.`status` AS `usuario_ativo`
    FROM
        (`curadores`
        LEFT JOIN `usuarios` ON ((`usuarios`.`id` = `curadores`.`fk_usuario_id`)))
    ORDER BY `curadores`.`id` DESC

CREATE VIEW `vw_usuarios_produtora` AS
    SELECT
        `produtora`.`id` AS `produtora_id`,
        `produtora`.`criacao` AS `registro`,
        `produtora`.`razao_social` AS `razao_social`,
        `produtora`.`fantasia` AS `nome_fantasia`,
        `produtora`.`cnpj` AS `cnpj`,
        `produtora`.`cpf` AS `cpf`,
        `produtora`.`responsavel` AS `responsavel`,
        `produtora`.`status` AS `regsitro_ativa`,
        `usuarios`.`status` AS `usuario_ativo`,
        `usuarios`.`email` AS `email`
    FROM
        (`produtora`
        LEFT JOIN `usuarios` ON ((`usuarios`.`id` = `produtora`.`fk_usuario_id`)))
    ORDER BY `produtora`.`id` DESC
