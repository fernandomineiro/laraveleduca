alter table usuarios add column aluno_kroton tinyint(4);
alter table pedidos_item add column fk_produto_externo_id int(11);
alter table pedidos add column data_compra_externa DATE NULL;
