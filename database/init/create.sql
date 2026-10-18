-- -----------------------------------------------------
-- Schema ar_app
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `ar_app` ;
CREATE SCHEMA IF NOT EXISTS `ar_app` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `ar_app` ;

-- -----------------------------------------------------
-- Table `ar_app`.`ar_markers`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `ar_app`.`ar_markers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `barcode_value` INT NOT NULL,
  `barcode_link` VARCHAR(1024) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 52
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `ar_app`.`galleries`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `ar_app`.`galleries` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(256) NOT NULL,
  `description` VARCHAR(1024) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 63
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `ar_app`.`paintings`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `ar_app`.`paintings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `gallery_id` INT NOT NULL,
  `marker_id` INT NOT NULL,
  `name` VARCHAR(256) NOT NULL,
  `author` VARCHAR(256) NOT NULL,
  `image_link` VARCHAR(1024) NOT NULL,
  PRIMARY KEY (`gallery_id`, `marker_id`),
  UNIQUE KEY (`id`),
  CONSTRAINT `fk_paintings_ar_markers`
    FOREIGN KEY (`marker_id`)
    REFERENCES `ar_app`.`ar_markers` (`id`),
  CONSTRAINT `fk_paintings_galleries`
    FOREIGN KEY (`gallery_id`)
    REFERENCES `ar_app`.`galleries` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 30
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

-- Uncomment the following line if you need a unique index on `id` in the `paintings` table:
-- CREATE UNIQUE INDEX `id_UNIQUE` ON `ar_app`.`paintings` (`id` ASC);

CREATE INDEX `fk_paintings_galleries_idx` ON `ar_app`.`paintings` (`gallery_id` ASC);

CREATE INDEX `fk_paintings_ar_markers_idx` ON `ar_app`.`paintings` (`marker_id` ASC);


-- -----------------------------------------------------
-- Table `ar_app`.`users`
-- -----------------------------------------------------

CREATE TABLE IF NOT EXISTS `ar_app`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(128) NOT NULL,
  `email` VARCHAR(516) NULL DEFAULT NULL,
  `hash` VARCHAR(1024) NOT NULL,
  `auth_token` VARCHAR(1024) NULL DEFAULT NULL,
  `gallery_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_users_galleries`
    FOREIGN KEY (`gallery_id`)
    REFERENCES `ar_app`.`galleries` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 56
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE UNIQUE INDEX `username_UNIQUE` ON `ar_app`.`users` (`username` ASC);
CREATE INDEX `fk_users_galleries_idx` ON `ar_app`.`users` (`gallery_id` ASC);
