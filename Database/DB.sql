-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`Admin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Admin` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Admin` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Username` VARCHAR(45) NOT NULL,
  `Password` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Tournament`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Tournament` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Tournament` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(45) NOT NULL,
  `IsLive` TINYINT(1) NOT NULL,
  PRIMARY KEY (`Id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Group` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Group` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(45) NOT NULL,
  `TournamentId` INT NOT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Group_Tournament1_idx` (`TournamentId` ASC),
  CONSTRAINT `fk_Group_Tournament1`
    FOREIGN KEY (`TournamentId`)
    REFERENCES `mydb`.`Tournament` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Team`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Team` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Team` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(100) NOT NULL,
  `TournamentId` INT NOT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Team_Tournament1_idx` (`TournamentId` ASC),
  CONSTRAINT `fk_Team_Tournament1`
    FOREIGN KEY (`TournamentId`)
    REFERENCES `mydb`.`Tournament` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`MatchInfo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`MatchInfo` ;

CREATE TABLE IF NOT EXISTS `mydb`.`MatchInfo` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `GroupId` INT NOT NULL,
  `TeamFirstId` INT NOT NULL,
  `TeamSecondId` INT NOT NULL,
  `TeamFirstPoints` INT ZEROFILL NOT NULL,
  `TeamSecondPoints` INT ZEROFILL NOT NULL,
  `MatchTime` DATETIME NOT NULL,
  `IsRunning` TINYINT(1) NOT NULL,
  `IsCompleted` TINYINT(1) NOT NULL,
  `TeamFirstAfterPoints` INT NULL,
  `TeamSecondAfterPoints` INT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Match_Group1_idx` (`GroupId` ASC),
  INDEX `fk_Match_Team1_idx` (`TeamFirstId` ASC),
  INDEX `fk_Match_Team2_idx` (`TeamSecondId` ASC),
  CONSTRAINT `fk_Match_Group1`
    FOREIGN KEY (`GroupId`)
    REFERENCES `mydb`.`Group` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Match_Team1`
    FOREIGN KEY (`TeamFirstId`)
    REFERENCES `mydb`.`Team` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Match_Team2`
    FOREIGN KEY (`TeamSecondId`)
    REFERENCES `mydb`.`Team` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`SettingsType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`SettingsType` ;

CREATE TABLE IF NOT EXISTS `mydb`.`SettingsType` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Settings` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Settings` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(100) NOT NULL,
  `Value` VARCHAR(100) NOT NULL,
  `TypeId` INT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Settings_SettingsType_idx` (`TypeId` ASC),
  CONSTRAINT `fk_Settings_SettingsType`
    FOREIGN KEY (`TypeId`)
    REFERENCES `mydb`.`SettingsType` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`DefaultMatchTempalteType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`DefaultMatchTempalteType` ;

CREATE TABLE IF NOT EXISTS `mydb`.`DefaultMatchTempalteType` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`Id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`DefaultMatchTemplate`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`DefaultMatchTemplate` ;

CREATE TABLE IF NOT EXISTS `mydb`.`DefaultMatchTemplate` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `TypeId` INT NOT NULL,
  `TeamFirstId` INT NOT NULL,
  `TeamSecondId` INT NOT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_DefaultMatchTemplate_DefaultMatchTempalteType1_idx` (`TypeId` ASC),
  CONSTRAINT `fk_DefaultMatchTemplate_DefaultMatchTempalteType1`
    FOREIGN KEY (`TypeId`)
    REFERENCES `mydb`.`DefaultMatchTempalteType` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Player`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Player` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Player` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(100) NULL,
  `Vorname` VARCHAR(100) NULL,
  `TeamId` INT NOT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Player_Team1_idx` (`TeamId` ASC),
  CONSTRAINT `fk_Player_Team1`
    FOREIGN KEY (`TeamId`)
    REFERENCES `mydb`.`Team` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Group_has_Team`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`Group_has_Team` ;

CREATE TABLE IF NOT EXISTS `mydb`.`Group_has_Team` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Group_Id` INT NOT NULL,
  `Team_Id` INT NOT NULL,
  PRIMARY KEY (`Id`),
  INDEX `fk_Group_has_Team_Team1_idx` (`Team_Id` ASC),
  INDEX `fk_Group_has_Team_Group1_idx` (`Group_Id` ASC),
  CONSTRAINT `fk_Group_has_Team_Group1`
    FOREIGN KEY (`Group_Id`)
    REFERENCES `mydb`.`Group` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Group_has_Team_Team1`
    FOREIGN KEY (`Team_Id`)
    REFERENCES `mydb`.`Team` (`Id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `mydb`.`Admin`
-- -----------------------------------------------------
START TRANSACTION;
USE `mydb`;
INSERT INTO `mydb`.`Admin` (`Id`, `Username`, `Password`) VALUES (1, 'megaforge', 'megaforge');

COMMIT;

