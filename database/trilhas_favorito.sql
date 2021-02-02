-- create table para favoritar trilha
create table trilhas_favorito(
   id INT AUTO_INCREMENT,
   fk_usuario int NOT NULL,
   fk_trilha int NOT NULL,
   PRIMARY KEY (id)
);
