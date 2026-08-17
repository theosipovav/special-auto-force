-- MySQL Database Schema for СПЕЦАВТОСИЛА
-- Yii2 + Bootstrap 5.3 + PHP 8.0

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `name` VARCHAR(64) DEFAULT NULL,
  `surname` VARCHAR(64) DEFAULT NULL,
  `patronymic` VARCHAR(64) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `auth_key` VARCHAR(32) DEFAULT NULL,
  `access_token` VARCHAR(255) DEFAULT NULL,
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  `status` SMALLINT NOT NULL DEFAULT 10,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_user_username` (`username`),
  UNIQUE INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(64) NOT NULL,
  `description` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_role_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `user_role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `user_role`;
CREATE TABLE `user_role` (
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  INDEX `idx_user_role_role_id` (`role_id`),
  CONSTRAINT `fk_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` INT DEFAULT NULL,
  `updated_at` INT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `short_description` TEXT DEFAULT NULL,
  `long_description` LONGTEXT DEFAULT NULL,
  `info` TEXT DEFAULT NULL,
  `created_at` INT DEFAULT NULL,
  `updated_at` INT DEFAULT NULL,
  `views` INT DEFAULT 0,
  `orders_count` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  FULLTEXT INDEX `idx_product_search` (`title`, `short_description`, `long_description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `product_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_category`;
CREATE TABLE `product_category` (
  `category_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`category_id`, `product_id`),
  INDEX `idx_product_category_product_id` (`product_id`),
  CONSTRAINT `fk_product_category_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_product_category_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `product_image`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_image`;
CREATE TABLE `product_image` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `title` VARCHAR(128) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `is_main` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_product_image_product_id` (`product_id`),
  CONSTRAINT `fk_product_image_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `parameter`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `parameter`;
CREATE TABLE `parameter` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `type` VARCHAR(32) DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_parameter_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` VARCHAR(32) DEFAULT 'new',
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_order_user_id` (`user_id`),
  INDEX `idx_order_product_id` (`product_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_order_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `request` (Заявка)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `request`;
CREATE TABLE `request` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `wishes` TEXT DEFAULT NULL,
  `status` VARCHAR(32) DEFAULT 'new',
  `created_at` INT NOT NULL,
  `updated_at` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_request_product_id` (`product_id`),
  INDEX `idx_request_user_id` (`user_id`),
  CONSTRAINT `fk_request_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_request_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insert default data
-- -----------------------------------------------------

-- Roles
INSERT INTO `role` (`title`, `description`) VALUES
('admin', 'Администратор сайта'),
('manager', 'Менеджер по продажам'),
('customer', 'Покупатель');

-- Default admin user (password: admin123)
INSERT INTO `user` (`username`, `password_hash`, `email`, `created_at`, `updated_at`, `status`) VALUES
('admin', '$2y$13$KpQJzN.xVqGvZ9YhLqXwWuR8FqYmN5jKpLxMnOpQrStUvWxYz.abc', 'admin@spetsavtosila.ru', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 10);

-- Assign admin role to admin user
INSERT INTO `user_role` (`user_id`, `role_id`) VALUES (1, 1);

-- Site parameters
INSERT INTO `parameter` (`title`, `value`, `type`) VALUES
('site_name', 'СПЕЦАВТОСИЛА', 'text'),
('site_phone', '+7 (XXX) XXX-XX-XX', 'text'),
('site_email', 'info@spetsavtosila.ru', 'email'),
('site_address', 'г. Москва, ул. Примерная, д. 1', 'text'),
('social_vk', 'https://vk.com/spetsavtosila', 'url'),
('social_telegram', 'https://t.me/spetsavtosila', 'url');

SET FOREIGN_KEY_CHECKS = 1;
