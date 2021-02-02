-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema dev_educaz
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema dev_educaz
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `dev_educaz` DEFAULT CHARACTER SET utf8 ;
USE `dev_educaz` ;

-- -----------------------------------------------------
-- Table `dev_educaz`.`cursos_tipo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cursos_tipo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`faculdades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`faculdades` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cursos_categoria`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cursos_categoria` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL DEFAULT 1,
  `fk_faculdade` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cursos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cursos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `fk_cursos_tipo` INT NOT NULL,
  `fk_cursos_categoria` INT NOT NULL,
  `fk_faculdade` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cursos_valor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cursos_valor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_cursos` INT NOT NULL,
  `valor` DECIMAL(12,2) NULL,
  `data_inicio` DATE NOT NULL,
  `data_validade` DATE NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cursos_modulos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cursos_modulos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  `tipo_modulo` INT NOT NULL,
  `url_video` TEXT NULL,
  `url_arquivo` TEXT NULL,
  `carga_horario` TIME NULL,
  `fk_curso` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`perfil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`perfil` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`usuarios` (
  `id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `login` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `fk_perfil` INT NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`certificados`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`certificados` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_curso` INT NOT NULL,
  `data_conclusao` DATE NULL,
  `fk_usuario` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`configuracoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`configuracoes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `dominio` VARCHAR(45) NULL,
  `logo` VARCHAR(255) NULL,
  `banner_home` VARCHAR(255) NULL,
  `cor_principal` VARCHAR(7) NULL,
  `cor_secundaria` VARCHAR(7) NULL,
  `fk_faculdade` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`eventos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`eventos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`agenda_evento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`agenda_evento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_evento` INT NOT NULL,
  `descricao` TEXT NULL,
  `data_inicio` DATE NOT NULL,
  `data_final` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_final` TIME NOT NULL,
  `valor` DECIMAL(12,2) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos_status` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `cor` VARCHAR(45) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cupom`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cupom` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `codigo_cupom` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `data_cadastro` DATE NOT NULL,
  `data_validade_inicial` DATE NULL,
  `data_validade_final` DATE NULL,
  `tipo_cupom_desconto` INT NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`tipos_pagamento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`tipos_pagamento` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos` (
  `id` INT NOT NULL,
  `fk_usuario` INT NOT NULL,
  `data_inclusao` DATETIME NOT NULL,
  `valor_bruto` DECIMAL(12,2) NOT NULL,
  `valor_desconto` DECIMAL(12,2) NOT NULL,
  `valor_imposto` DECIMAL(12,2) NOT NULL,
  `valor_liquido` DECIMAL(12,2) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cupom_pedido`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cupom_pedido` (
  `id` INT NOT NULL,
  `fk_pedido` INT NOT NULL,
  `fk_cupom` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos_item` (
  `id` INT NOT NULL,
  `valor_bruto` VARCHAR(45) NULL,
  `valor_desconto` VARCHAR(45) NULL,
  `valor_imposto` VARCHAR(45) NULL,
  `valor_liquido` VARCHAR(45) NULL,
  `status` VARCHAR(45) NULL,
  `fk_pedido` INT(11) NOT NULL,
  `fk_curso` INT(11) NULL,
  `fk_evento` INT(11) NOT NULL,
  `tipo_item` TINYINT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`eventos1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`eventos1` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`agenda_evento1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`agenda_evento1` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `fk_evento` INT NOT NULL,
  `descricao` TEXT NULL,
  `data_inicio` DATE NOT NULL,
  `data_final` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_final` TIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`perfil1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`perfil1` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`usuarios1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`usuarios1` (
  `id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `login` VARCHAR(255) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `fk_perfil` INT NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos_status1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos_status1` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `cor` VARCHAR(45) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cupom1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cupom1` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `codigo_cupom` VARCHAR(255) NOT NULL,
  `data_cadastro` DATETIME NOT NULL,
  `data_validade_inicial` DATETIME NULL,
  `data_validade_final` DATETIME NULL,
  `tipo_cupom_desconto` FLOAT NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`tipos_pagamento1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`tipos_pagamento1` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos1` (
  `id` INT NOT NULL,
  `fk_usuario` INT NOT NULL,
  `data_inclusao` DATETIME NOT NULL,
  `valor_bruto` DECIMAL(12,2) NOT NULL,
  `valor_desconto` DECIMAL(12,2) NOT NULL,
  `valor_imposto` DECIMAL(12,2) NOT NULL,
  `valor_liquido` DECIMAL(12,2) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`cupom_pedido1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`cupom_pedido1` (
  `id` INT NOT NULL,
  `fk_pedido` INT NOT NULL,
  `fk_cupom` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos_item1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos_item1` (
  `id` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pagamento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pagamento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `valor_pagamento` FLOAT(9,2) NOT NULL,
  `data_pagamento` DATE NULL,
  `fk_pedido` INT NOT NULL,
  `fk_tipo_pagamento` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`pedidos_historico_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`pedidos_historico_status` (
  `id` INT NOT NULL,
  `status` VARCHAR(45) NULL,
  `data_inclusao` VARCHAR(45) NULL,
  `fk_pedido_status` INT NOT NULL,
  `fk_pedido` INT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`nacionalidade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`nacionalidade` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(45) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`banco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`banco` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `numero` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`professor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`professor` (
  `id` INT NOT NULL,
  `profissao` VARCHAR(255) NOT NULL,
  `tipo_proposta` TINYINT NOT NULL,
  `mini_curriculum` TEXT NOT NULL,
  `foto` VARCHAR(255) NOT NULL,
  `fk_usuario` INT NOT NULL,
  `razao_social` VARCHAR(255) NOT NULL,
  `endereco` VARCHAR(255) NOT NULL,
  `fk_nacionalidade` INT NOT NULL,
  `cep` VARCHAR(45) NOT NULL,
  `cpf` VARCHAR(11) NULL,
  `cnpj` VARCHAR(14) NULL,
  `representante` VARCHAR(255) NULL,
  `fk_banco` INT NULL,
  `agencia` VARCHAR(45) NULL,
  `conta_corrente` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas_status` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas` (
  `id` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NOT NULL,
  `url_video` VARCHAR(255) NULL,
  `duracao_total` TIME NULL,
  `fk_professor` INT NOT NULL,
  `fk_proposta_status` INT NOT NULL,
  `local` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas_historico_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas_historico_status` (
  `id` INT NOT NULL,
  `fk_proposta` INT NOT NULL,
  `fk_usuario` INT NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`proposta_modulos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`proposta_modulos` (
  `id` INT NOT NULL,
  `fk_proposta` INT NOT NULL,
  `ordem_modulo` TINYINT NOT NULL,
  `url_video` VARCHAR(255) NULL,
  `arquivo` VARCHAR(255) NULL,
  `duracao` TIME NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas_sugestoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas_sugestoes` (
  `id` INT NOT NULL,
  `preco` DECIMAL(12,2) NOT NULL,
  `fk_proposta` INT NOT NULL,
  `categoria` VARCHAR(255) NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas_sugestoes_questionarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas_sugestoes_questionarios` (
  `id` INT NOT NULL,
  `tipo_questionario` VARCHAR(255) NOT NULL,
  `fk_proposta_sugestao` INT NOT NULL,
  `questao` TEXT NOT NULL,
  `ordem` TINYINT NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`propostas_sugestoes_questionario_opcoes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`propostas_sugestoes_questionario_opcoes` (
  `id` INT NOT NULL,
  `fk_proposta_sugestao_questionario` INT NOT NULL,
  `descricao` TEXT NOT NULL,
  `ordem` TINYINT NOT NULL,
  `status` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`proposta_agenda`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`proposta_agenda` (
  `id` INT NOT NULL,
  `fk_proposta` INT NOT NULL,
  `data_aula` DATE NOT NULL,
  `inicio` TIME NOT NULL,
  `termino` TIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dev_educaz`.`table1`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `dev_educaz`.`table1` (
)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
