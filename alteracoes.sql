CREATE TABLE `lemarq`.`cliente` (`id` INT NOT NULL AUTO_INCREMENT , `nome` VARCHAR(255) NULL DEFAULT NULL , `data_nascimento` DATE NULL DEFAULT NULL , `sexo_id` INT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `lemarq`.`sexo` (`id` INT NULL AUTO_INCREMENT , `descricao` VARCHAR(255) NULL DEFAULT NULL , `abreviacao` CHAR(1) NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `cliente` ADD FOREIGN KEY (`sexo_id`) REFERENCES `sexo`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
CREATE TABLE `lemarq`.`produto` (`id` INT NOT NULL AUTO_INCREMENT , `descricao` VARCHAR(255) NULL DEFAULT NULL , `valor_compra` DOUBLE(10,2) NULL DEFAULT NULL , `valor_venda` DOUBLE(10,2) NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
CREATE TABLE `lemarq`.`pedido` (`id` INT NOT NULL AUTO_INCREMENT , `cliente_id` INT NULL DEFAULT NULL , `data_cadastro` DATETIME NULL DEFAULT NULL , `usuario_cadastro_id` INT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `pedido` ADD FOREIGN KEY (`cliente_id`) REFERENCES `cliente`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
CREATE TABLE `lemarq`.`pedido_produto` (`id` INT NOT NULL AUTO_INCREMENT , `pedido_id` INT NULL DEFAULT NULL , `produto_id` INT NULL DEFAULT NULL , `valor_compra` DOUBLE NULL DEFAULT NULL , `valor_venda` DOUBLE NULL DEFAULT NULL , `data_cadastro` DATETIME NULL DEFAULT NULL , `usuario_cadastro_id` INT NULL DEFAULT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `pedido_produto` ADD FOREIGN KEY (`pedido_id`) REFERENCES `pedido`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE `pedido_produto` ADD FOREIGN KEY (`produto_id`) REFERENCES `produto`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
INSERT INTO `sexo` (`id`, `descricao`, `abreviacao`) VALUES ('1', 'Masculino', 'M'), ('2', 'Feminino', 'F');

-- Base de dados de log
ALTER TABLE `system_change_log` ADD `system_user_id` INT NULL DEFAULT NULL AFTER `log_date`;

