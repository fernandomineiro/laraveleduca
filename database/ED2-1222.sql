create table cupom_assinaturas(
   id INT AUTO_INCREMENT,
   fk_assinatura int NOT NULL,
   fk_faculdade int,
   fk_cupom int NOT NULL,
   PRIMARY KEY (id)
);

create table cupom_eventos(
   id INT AUTO_INCREMENT,
   fk_evento int NOT NULL,
   fk_faculdade int,
   fk_cupom int NOT NULL,
   PRIMARY KEY (id)
);
