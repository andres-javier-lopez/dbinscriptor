SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE( `username` )
)
ENGINE = InnoDB;

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id_project` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `database_name` VARCHAR(50) NOT NULL,
  `project_route` VARCHAR(300) NOT NULL,
  `database_charset` VARCHAR(50) NOT NULL DEFAULT 'utf8_unicode_ci',
  `database_version` VARCHAR(20) NOT NULL DEFAULT '0.0.0',
  PRIMARY KEY (`id_project`),
  UNIQUE( `database_name` ) )
ENGINE = InnoDB;

DROP TABLE IF EXISTS `install_scripts`;
CREATE TABLE IF NOT EXISTS `install_scripts` (
  `id_script` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_project` INT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL DEFAULT 'baseline',
  PRIMARY KEY (`id_script`),
  INDEX `ix_project_script` (`id_project` ASC),
  CONSTRAINT `fk_project_script`
    FOREIGN KEY (`id_project`)
    REFERENCES `projects` (`id_project`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION )
ENGINE = InnoDB;

DROP TABLE IF EXISTS `projects_changelog` ;
CREATE  TABLE IF NOT EXISTS `projects_changelog` (
  `id_project_changelog` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_project` INT UNSIGNED NOT NULL,
  `major_version` INT NULL,
  `minor_version` INT NULL,
  `point_version` INT NULL,
  `script_file` VARCHAR(400) NULL,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_project_changelog`),
  INDEX `ix_project_changelog` (`id_project` ASC),
  CONSTRAINT `fk_project_changelog`
    FOREIGN KEY (`id_project`)
    REFERENCES `projects` (`id_project`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION )
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
