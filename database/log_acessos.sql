create table log_acessos(
  id INT AUTO_INCREMENT,
  fk_usuario int NOT NULL,
  ip_acesso int NOT NULL,
  data_acesso datetime,
  user_agent_acesso varchar(256),
  PRIMARY KEY (id)
);
