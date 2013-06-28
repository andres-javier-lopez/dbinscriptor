SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `changelog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `changelog` ;

CREATE  TABLE IF NOT EXISTS `changelog` (
  `id_changelog` INT NOT NULL AUTO_INCREMENT ,
  `major_version` INT NULL ,
  `minor_version` INT NULL ,
  `point_version` INT NULL ,
  `script_file` VARCHAR(100) NULL ,
  `timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id_changelog`) )
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
