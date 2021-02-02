
/**
 *  Nova tabela PROFESSOR_FORMACAO_TIPO 
 *  e insert de possíveis tipos padrão
 */ 

-- DROP TABLE professor_formacao_tipo;
CREATE TABLE professor_formacao_tipo 
(
    id INT NOT NULL AUTO_INCREMENT,
    tipo TEXT NOT NULL,
    status TINYINT DEFAULT 1,    
    PRIMARY KEY (id)
);

INSERT INTO professor_formacao_tipo (tipo) 
VALUES 
    ('Graduação'), 
    ('Bacharelado'), 
    ('Licenciatura'), 
    ('Pós-Graduação'), 
    ('Mestrado'),
    ('Doutorado'), 
    ('Pós-doutorado'), 
    ('Especialização'),
    ('MBA');


/**
 *  Nova tabela PROFESSOR_FORMACAO
 */ 

-- DROP TABLE professor_formacao
CREATE TABLE professor_formacao
(
    id INT NOT NULL AUTO_INCREMENT,
    fk_professor_formacao_tipo_id INT NOT NULL,
    fk_professor_id INT NOT NULL,
    instituicao TEXT NOT NULL,
    curso TEXT NOT NULL,
    ano_inicio YEAR(4),
    ano_conclusao YEAR(4),
    PRIMARY KEY (id),
    FOREIGN KEY (fk_professor_formacao_tipo_id)
        REFERENCES professor_formacao_tipo(id),
    FOREIGN KEY (fk_professor_id)
        REFERENCES professor(id)
        ON DELETE CASCADE
);


/**
 *  Alterações na tabela de PROPOSTAS
 */ 

SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE propostas ADD COLUMN fk_categoria_id INT NOT NULL;
ALTER TABLE propostas ADD CONSTRAINT fk_categoria FOREIGN KEY (fk_categoria_id) REFERENCES cursos_categoria(id);
ALTER TABLE propostas ADD COLUMN objetivo TEXT;
ALTER TABLE propostas ADD COLUMN publico_alvo TEXT;
ALTER TABLE propostas MODIFY local TEXT NULL;
ALTER TABLE propostas MODIFY status TINYINT DEFAULT 1;


/**
 *  Alterações na tabela de PROPOSTA_MODULOS
 */ 
ALTER TABLE proposta_modulos ADD COLUMN titulo TEXT NOT NULL;


ALTER TABLE professor ADD COLUMN sobrenome TEXT;
ALTER TABLE professor ADD COLUMN data_nascimento DATE;
ALTER TABLE professor ADD COLUMN complemento TEXT;
ALTER TABLE professor MODIFY endereco TEXT NULL;
