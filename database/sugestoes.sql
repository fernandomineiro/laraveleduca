
/**
 *  Nova tabela sugestão de cursos 
 *  e insert de possíveis tipos padrão
 */ 

-- DROP TABLE cursos_sugestao;
CREATE TABLE cursos_sugestao 
(
    id INT NOT NULL AUTO_INCREMENT,
    objetivo TEXT NOT NULL,
    profissao TEXT NOT NULL,
    fk_categoria_id INT,
    categoria TEXT,
    tempo_dia TEXT,
    tempo_prazo TEXT,
    tipo_certificado ENUM('individual', 'geral'),
    PRIMARY KEY (id),
    FOREIGN KEY (fk_categoria_id) REFERENCES cursos_categoria(id)
);
