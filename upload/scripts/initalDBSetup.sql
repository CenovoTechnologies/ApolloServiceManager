-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema asm
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema asm
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `asm` DEFAULT CHARACTER SET utf8 ;
USE `asm` ;

-- -----------------------------------------------------
-- Table `asm`.`asm__search`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm__search` (
  `object_type` VARCHAR(8) NOT NULL,
  `object_id` INT(11) UNSIGNED NOT NULL,
  `title` TEXT NULL DEFAULT NULL,
  `content` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`object_type`, `object_id`),
  FULLTEXT INDEX `search` (`title` ASC, `content` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_api_key`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_api_key` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) NOT NULL DEFAULT '1',
  `ipaddr` VARCHAR(64) NOT NULL,
  `apikey` VARCHAR(255) NOT NULL,
  `can_create_tickets` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `can_exec_cron` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `notes` TEXT NULL DEFAULT NULL,
  `updated` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `apikey` (`apikey` ASC),
  INDEX `ipaddr` (`ipaddr` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_attachment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_attachment` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_id` INT(11) UNSIGNED NOT NULL,
  `type` CHAR(1) NOT NULL,
  `file_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  `inline` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `lang` VARCHAR(16) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `file-type` (`object_id` ASC, `file_id` ASC, `type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 13
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_auto_closure`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_auto_closure` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `time_period` INT(10) UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(64) NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_canned_response`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_canned_response` (
  `canned_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `isenabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `response` TEXT NOT NULL,
  `lang` VARCHAR(16) NOT NULL DEFAULT 'en_US',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`canned_id`),
  UNIQUE INDEX `title` (`title` ASC),
  INDEX `dept_id` (`dept_id` ASC),
  INDEX `active` (`isenabled` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_config`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_config` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `namespace` VARCHAR(64) NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NOT NULL,
  `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `namespace` (`namespace` ASC, `key` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 140
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_content`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_content` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `type` VARCHAR(32) NOT NULL DEFAULT 'other',
  `name` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 13
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_department`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_department` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) UNSIGNED NULL DEFAULT NULL,
  `tpl_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sla_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `email_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `autoresp_email_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `manager_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `signature` TEXT NOT NULL,
  `ispublic` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `group_membership` TINYINT(1) NOT NULL DEFAULT '0',
  `ticket_auto_response` TINYINT(1) NOT NULL DEFAULT '1',
  `message_auto_response` TINYINT(1) NOT NULL DEFAULT '0',
  `path` VARCHAR(128) NOT NULL DEFAULT '/',
  `updated` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name` ASC, `pid` ASC),
  INDEX `manager_id` (`manager_id` ASC),
  INDEX `autoresp_email_id` (`autoresp_email_id` ASC),
  INDEX `tpl_id` (`tpl_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_draft`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_draft` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INT(11) UNSIGNED NOT NULL,
  `namespace` VARCHAR(32) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `extra` TEXT NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_email` (
  `email_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `noautoresp` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `priority_id` TINYINT(3) UNSIGNED NOT NULL DEFAULT '2',
  `dept_id` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `topic_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `email` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `userid` VARCHAR(255) NOT NULL,
  `userpass` VARCHAR(255) CHARACTER SET 'ascii' NOT NULL,
  `mail_active` TINYINT(1) NOT NULL DEFAULT '0',
  `mail_host` VARCHAR(255) NOT NULL,
  `mail_protocol` ENUM('POP', 'IMAP') NOT NULL DEFAULT 'POP',
  `mail_encryption` ENUM('NONE', 'SSL') NOT NULL,
  `mail_port` INT(6) NULL DEFAULT NULL,
  `mail_fetchfreq` TINYINT(3) NOT NULL DEFAULT '5',
  `mail_fetchmax` TINYINT(4) NOT NULL DEFAULT '30',
  `mail_archivefolder` VARCHAR(255) NULL DEFAULT NULL,
  `mail_delete` TINYINT(1) NOT NULL DEFAULT '0',
  `mail_errors` TINYINT(3) NOT NULL DEFAULT '0',
  `mail_lasterror` DATETIME NULL DEFAULT NULL,
  `mail_lastfetch` DATETIME NULL DEFAULT NULL,
  `smtp_active` TINYINT(1) NULL DEFAULT '0',
  `smtp_host` VARCHAR(255) NOT NULL,
  `smtp_port` INT(6) NULL DEFAULT NULL,
  `smtp_secure` TINYINT(1) NOT NULL DEFAULT '1',
  `smtp_auth` TINYINT(1) NOT NULL DEFAULT '1',
  `smtp_spoofing` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE INDEX `email` (`email` ASC),
  INDEX `priority_id` (`priority_id` ASC),
  INDEX `dept_id` (`dept_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_email_account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_email_account` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `protocol` VARCHAR(64) NOT NULL DEFAULT '',
  `host` VARCHAR(128) NOT NULL DEFAULT '',
  `port` INT(11) NOT NULL,
  `username` VARCHAR(128) NULL DEFAULT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `options` VARCHAR(512) NULL DEFAULT NULL,
  `errors` INT(11) UNSIGNED NULL DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `lastconnect` TIMESTAMP NULL DEFAULT NULL,
  `lasterror` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_email_template`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_email_template` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tpl_id` INT(11) UNSIGNED NOT NULL,
  `code_name` VARCHAR(32) NOT NULL,
  `subject` VARCHAR(255) NOT NULL DEFAULT '',
  `body` TEXT NOT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `template_lookup` (`tpl_id` ASC, `code_name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 20
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_email_template_group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_email_template_group` (
  `tpl_id` INT(11) NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `lang` VARCHAR(16) NOT NULL DEFAULT 'en_US',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tpl_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_faq`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_faq` (
  `faq_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `ispublished` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `keywords` TINYTEXT NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`faq_id`),
  UNIQUE INDEX `question` (`question` ASC),
  INDEX `category_id` (`category_id` ASC),
  INDEX `ispublished` (`ispublished` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_faq_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_faq_category` (
  `category_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ispublic` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(125) NULL DEFAULT NULL,
  `description` TEXT NOT NULL,
  `notes` TINYTEXT NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`category_id`),
  INDEX `ispublic` (`ispublic` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_faq_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_faq_topic` (
  `faq_id` INT(10) UNSIGNED NOT NULL,
  `topic_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`faq_id`, `topic_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_file`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_file` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ft` CHAR(1) NOT NULL DEFAULT 'T',
  `bk` CHAR(1) NOT NULL DEFAULT 'D',
  `type` VARCHAR(255) CHARACTER SET 'ascii' NOT NULL DEFAULT '',
  `size` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `key` VARCHAR(86) CHARACTER SET 'ascii' NOT NULL,
  `signature` VARCHAR(86) CHARACTER SET 'ascii' NOT NULL,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `attrs` VARCHAR(255) NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `ft` (`ft` ASC),
  INDEX `key` (`key` ASC),
  INDEX `signature` (`signature` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_file_chunk`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_file_chunk` (
  `file_id` INT(11) NOT NULL,
  `chunk_id` INT(11) NOT NULL,
  `filedata` LONGBLOB NOT NULL,
  PRIMARY KEY (`file_id`, `chunk_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_filter`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_filter` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `execorder` INT(10) UNSIGNED NOT NULL DEFAULT '99',
  `isactive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `match_all_rules` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `stop_onmatch` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `target` ENUM('Any', 'Web', 'Email', 'API') NOT NULL DEFAULT 'Any',
  `email_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(32) NOT NULL DEFAULT '',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `target` (`target` ASC),
  INDEX `email_id` (`email_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_filter_action`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_filter_action` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filter_id` INT(10) UNSIGNED NOT NULL,
  `sort` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `type` VARCHAR(24) NOT NULL,
  `configuration` TEXT NULL DEFAULT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `filter_id` (`filter_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_filter_rule`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_filter_rule` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filter_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `what` VARCHAR(32) NOT NULL,
  `how` ENUM('equal', 'not_equal', 'contains', 'dn_contain', 'starts', 'ends', 'match', 'not_match') NOT NULL,
  `val` VARCHAR(255) NOT NULL,
  `isactive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `notes` TINYTEXT NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `filter` (`filter_id` ASC, `what` ASC, `how` ASC, `val` ASC),
  INDEX `filter_id` (`filter_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_form`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_form` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(10) UNSIGNED NULL DEFAULT NULL,
  `type` VARCHAR(8) NOT NULL DEFAULT 'G',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `title` VARCHAR(255) NOT NULL,
  `instructions` VARCHAR(512) NULL DEFAULT NULL,
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 7
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_form_entry`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_form_entry` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) UNSIGNED NOT NULL,
  `object_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `object_type` CHAR(1) NOT NULL DEFAULT 'T',
  `sort` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  `extra` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `entry_lookup` (`object_type` ASC, `object_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 17
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_form_entry_values`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_form_entry_values` (
  `entry_id` INT(11) UNSIGNED NOT NULL,
  `field_id` INT(11) UNSIGNED NOT NULL,
  `value` TEXT NULL DEFAULT NULL,
  `value_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`entry_id`, `field_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_form_field`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_form_field` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `form_id` INT(11) UNSIGNED NOT NULL,
  `flags` INT(10) UNSIGNED NULL DEFAULT '1',
  `type` VARCHAR(255) NOT NULL DEFAULT 'text',
  `label` VARCHAR(255) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `configuration` TEXT NULL DEFAULT NULL,
  `sort` INT(11) UNSIGNED NOT NULL,
  `hint` VARCHAR(512) NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 38
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_group` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT(11) UNSIGNED NOT NULL,
  `flags` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  `name` VARCHAR(120) NOT NULL DEFAULT '',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `role_id` (`role_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_help_topic`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_help_topic` (
  `topic_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_pid` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `isactive` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `ispublic` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `noautoresp` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NULL DEFAULT '0',
  `status_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `priority_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `team_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sla_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `page_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sequence_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sort` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `topic` VARCHAR(32) NOT NULL DEFAULT '',
  `number_format` VARCHAR(32) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`topic_id`),
  UNIQUE INDEX `topic` (`topic` ASC, `topic_pid` ASC),
  INDEX `topic_pid` (`topic_pid` ASC),
  INDEX `priority_id` (`priority_id` ASC),
  INDEX `dept_id` (`dept_id` ASC),
  INDEX `staff_id` (`staff_id` ASC, `team_id` ASC),
  INDEX `sla_id` (`sla_id` ASC),
  INDEX `page_id` (`page_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 12
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_help_topic_form`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_help_topic_form` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `form_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sort` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `extra` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `topic-form` (`topic_id` ASC, `form_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_list`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_list` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `name_plural` VARCHAR(255) NULL DEFAULT NULL,
  `sort_mode` ENUM('Alpha', '-Alpha', 'SortCol') NOT NULL DEFAULT 'Alpha',
  `masks` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `type` VARCHAR(16) NULL DEFAULT NULL,
  `configuration` TEXT NOT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `type` (`type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_list_items`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_list_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `list_id` INT(11) NULL DEFAULT NULL,
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '1',
  `value` VARCHAR(255) NOT NULL,
  `extra` VARCHAR(255) NULL DEFAULT NULL,
  `sort` INT(11) NOT NULL DEFAULT '1',
  `properties` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `list_item_lookup` (`list_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_lock`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_lock` (
  `lock_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `expire` DATETIME NULL DEFAULT NULL,
  `code` VARCHAR(20) NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`lock_id`),
  INDEX `staff_id` (`staff_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_note`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_note` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) UNSIGNED NULL DEFAULT NULL,
  `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `ext_id` VARCHAR(10) NULL DEFAULT NULL,
  `body` TEXT NULL DEFAULT NULL,
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `ext_id` (`ext_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_organization`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_organization` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `manager` VARCHAR(16) NOT NULL DEFAULT '',
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `domain` VARCHAR(256) NOT NULL DEFAULT '',
  `extra` TEXT NULL DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_organization__cdata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_organization__cdata` (
  `org_id` INT(11) UNSIGNED NOT NULL,
  `name` MEDIUMTEXT NULL DEFAULT NULL,
  `address` MEDIUMTEXT NULL DEFAULT NULL,
  `phone` MEDIUMTEXT NULL DEFAULT NULL,
  `website` MEDIUMTEXT NULL DEFAULT NULL,
  `notes` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`org_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_plugin`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_plugin` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `install_path` VARCHAR(60) NOT NULL,
  `isphar` TINYINT(1) NOT NULL DEFAULT '0',
  `isactive` TINYINT(1) NOT NULL DEFAULT '0',
  `version` VARCHAR(64) NULL DEFAULT NULL,
  `installed` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_queue`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_queue` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `title` VARCHAR(60) NULL DEFAULT NULL,
  `config` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_resolution_code`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_resolution_code` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(32) NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_role` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `name` VARCHAR(64) NULL DEFAULT NULL,
  `permissions` TEXT NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_sequence`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_sequence` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NULL DEFAULT NULL,
  `flags` INT(10) UNSIGNED NULL DEFAULT NULL,
  `next` BIGINT(20) UNSIGNED NOT NULL DEFAULT '1',
  `increment` INT(11) NULL DEFAULT '1',
  `padding` CHAR(1) NULL DEFAULT '0',
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_service`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_service` (
  `service_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_pid` INT(10) UNSIGNED NULL DEFAULT NULL,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `ispublic` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `noautoresp` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `flags` INT(10) UNSIGNED NULL DEFAULT NULL,
  `status_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `prioroty_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `dept_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `staff_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `team_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `sla_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `page_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `sequene_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service` VARCHAR(32) NULL DEFAULT NULL,
  `notes` MEDIUMTEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`service_id`),
  UNIQUE INDEX `service_id_UNIQUE` (`service_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_service_cat`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_service_cat` (
  `service_cat_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_cat_pid` INT(10) UNSIGNED NOT NULL,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `ispublic` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service_cat` VARCHAR(32) NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`service_cat_id`),
  UNIQUE INDEX `service_cat_id_UNIQUE` (`service_cat_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_service_sub_cat`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_service_sub_cat` (
  `service_sub_cat_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_sub_cat_pid` INT(10) UNSIGNED NOT NULL,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `ispublic` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service_sub_cat` VARCHAR(32) NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`service_sub_cat_id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_service_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_service_type` (
  `service_type_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `ispublic` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
  `dept_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `page_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `sort` INT(10) UNSIGNED NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `service_type` VARCHAR(32) NULL DEFAULT NULL,
  `created` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`service_type_id`),
  UNIQUE INDEX `service_type_id_UNIQUE` (`service_type_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_session` (
  `session_id` VARCHAR(255) CHARACTER SET 'ascii' NOT NULL DEFAULT '',
  `session_data` BLOB NULL DEFAULT NULL,
  `session_expire` DATETIME NULL DEFAULT NULL,
  `session_updated` DATETIME NULL DEFAULT NULL,
  `user_id` VARCHAR(16) CHARACTER SET 'utf8' NOT NULL DEFAULT '0' COMMENT 'osTicket staff/client ID',
  `user_ip` VARCHAR(64) CHARACTER SET 'utf8' NOT NULL,
  `user_agent` VARCHAR(255) CHARACTER SET 'utf8' NOT NULL,
  PRIMARY KEY (`session_id`),
  INDEX `updated` (`session_updated` ASC),
  INDEX `user_id` (`user_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `asm`.`asm_sla`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_sla` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '3',
  `grace_period` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(64) NOT NULL DEFAULT '',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_staff`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_staff` (
  `staff_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `role_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `username` VARCHAR(32) NOT NULL DEFAULT '',
  `firstname` VARCHAR(32) NULL DEFAULT NULL,
  `lastname` VARCHAR(32) NULL DEFAULT NULL,
  `passwd` VARCHAR(128) NULL DEFAULT NULL,
  `backend` VARCHAR(32) NULL DEFAULT NULL,
  `email` VARCHAR(128) NULL DEFAULT NULL,
  `phone` VARCHAR(24) NOT NULL DEFAULT '',
  `phone_ext` VARCHAR(6) NULL DEFAULT NULL,
  `mobile` VARCHAR(24) NOT NULL DEFAULT '',
  `signature` TEXT NOT NULL,
  `lang` VARCHAR(16) NULL DEFAULT NULL,
  `timezone` VARCHAR(64) NULL DEFAULT NULL,
  `locale` VARCHAR(16) NULL DEFAULT NULL,
  `notes` TEXT NULL DEFAULT NULL,
  `isactive` TINYINT(1) NOT NULL DEFAULT '1',
  `isadmin` TINYINT(1) NOT NULL DEFAULT '0',
  `isvisible` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  `onvacation` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `assigned_only` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `show_assigned_tickets` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `change_passwd` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `max_page_size` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `auto_refresh_rate` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `default_signature_type` ENUM('none', 'mine', 'dept') NOT NULL DEFAULT 'none',
  `default_paper_size` ENUM('Letter', 'Legal', 'Ledger', 'A4', 'A3') NOT NULL DEFAULT 'Letter',
  `extra` TEXT NULL DEFAULT NULL,
  `permissions` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `lastlogin` DATETIME NULL DEFAULT NULL,
  `passwdreset` DATETIME NULL DEFAULT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE INDEX `username` (`username` ASC),
  INDEX `dept_id` (`dept_id` ASC),
  INDEX `issuperuser` (`isadmin` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_staff_dept_access`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_staff_dept_access` (
  `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `role_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`staff_id`, `dept_id`),
  INDEX `dept_id` (`dept_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_syslog`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_syslog` (
  `log_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_type` ENUM('Debug', 'Warning', 'Error') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `log` TEXT NOT NULL,
  `logger` VARCHAR(64) NOT NULL,
  `ip_address` VARCHAR(64) NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`log_id`),
  INDEX `log_type` (`log_type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 408
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_task` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_id` INT(11) NOT NULL DEFAULT '0',
  `object_type` CHAR(1) NOT NULL,
  `number` VARCHAR(20) NULL DEFAULT NULL,
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `team_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `lock_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `duedate` DATETIME NULL DEFAULT NULL,
  `closed` DATETIME NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `dept_id` (`dept_id` ASC),
  INDEX `staff_id` (`staff_id` ASC),
  INDEX `team_id` (`team_id` ASC),
  INDEX `created` (`created` ASC),
  INDEX `object` (`object_id` ASC, `object_type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_task__cdata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_task__cdata` (
  `task_id` INT(11) UNSIGNED NOT NULL,
  `title` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`task_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_team`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_team` (
  `team_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '1',
  `name` VARCHAR(125) NOT NULL DEFAULT '',
  `notes` TEXT NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE INDEX `name` (`name` ASC),
  INDEX `lead_id` (`lead_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_team_member`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_team_member` (
  `team_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(10) UNSIGNED NOT NULL,
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`team_id`, `staff_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_thread`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_thread` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_id` INT(11) UNSIGNED NOT NULL,
  `object_type` CHAR(1) NOT NULL,
  `extra` TEXT NULL DEFAULT NULL,
  `lastresponse` DATETIME NULL DEFAULT NULL,
  `lastmessage` DATETIME NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `object_id` (`object_id` ASC),
  INDEX `object_type` (`object_type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 10
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_thread_collaborator`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_thread_collaborator` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `isactive` TINYINT(1) NOT NULL DEFAULT '1',
  `thread_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `role` CHAR(1) NOT NULL DEFAULT 'M',
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `collab` (`thread_id` ASC, `user_id` ASC),
  INDEX `user_id` (`user_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_thread_entry`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_thread_entry` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `thread_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `type` CHAR(1) NOT NULL DEFAULT '',
  `flags` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `poster` VARCHAR(128) NOT NULL DEFAULT '',
  `editor` INT(10) UNSIGNED NULL DEFAULT NULL,
  `editor_type` CHAR(1) NULL DEFAULT NULL,
  `source` VARCHAR(32) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `body` TEXT NOT NULL,
  `format` VARCHAR(16) NOT NULL DEFAULT 'html',
  `ip_address` VARCHAR(64) NOT NULL DEFAULT '',
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `pid` (`pid` ASC),
  INDEX `thread_id` (`thread_id` ASC),
  INDEX `staff_id` (`staff_id` ASC),
  INDEX `type` (`type` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 26
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_thread_entry_email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_thread_entry_email` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `thread_entry_id` INT(11) UNSIGNED NOT NULL,
  `mid` VARCHAR(255) NOT NULL,
  `headers` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `thread_entry_id` (`thread_entry_id` ASC),
  INDEX `mid` (`mid` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_thread_event`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_thread_event` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `thread_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `staff_id` INT(11) UNSIGNED NOT NULL,
  `team_id` INT(11) UNSIGNED NOT NULL,
  `dept_id` INT(11) UNSIGNED NOT NULL,
  `topic_id` INT(11) UNSIGNED NOT NULL,
  `state` ENUM('created', 'closed', 'reopened', 'assigned', 'transferred', 'overdue', 'edited', 'viewed', 'error', 'collab', 'resent') NOT NULL,
  `data` VARCHAR(1024) NULL DEFAULT NULL COMMENT 'Encoded differences',
  `username` VARCHAR(128) NOT NULL DEFAULT 'SYSTEM',
  `uid` INT(11) UNSIGNED NULL DEFAULT NULL,
  `uid_type` CHAR(1) NOT NULL DEFAULT 'S',
  `annulled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `timestamp` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `ticket_state` (`thread_id` ASC, `state` ASC, `timestamp` ASC),
  INDEX `ticket_stats` (`timestamp` ASC, `state` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 72
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket` (
  `ticket_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `number` VARCHAR(20) NULL DEFAULT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `user_email_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `status_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `dept_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `sla_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `service_type_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service_cat_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `service_sub_cat_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `topic_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `auto_close_plan_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `resolution_code_id` INT(10) UNSIGNED NULL DEFAULT NULL,
  `staff_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `team_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `email_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `lock_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `ip_address` VARCHAR(64) NOT NULL DEFAULT '',
  `source` ENUM('Web', 'Email', 'Phone', 'API', 'Other') NOT NULL DEFAULT 'Other',
  `source_extra` VARCHAR(40) NULL DEFAULT NULL,
  `isoverdue` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `isanswered` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `duedate` DATETIME NULL DEFAULT NULL,
  `est_duedate` DATETIME NULL DEFAULT NULL,
  `reopened` DATETIME NULL DEFAULT NULL,
  `resolved` DATETIME NULL DEFAULT NULL,
  `closed` DATETIME NULL DEFAULT NULL,
  `lastupdate` DATETIME NULL DEFAULT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`ticket_id`),
  INDEX `user_id` (`user_id` ASC),
  INDEX `dept_id` (`dept_id` ASC),
  INDEX `staff_id` (`staff_id` ASC),
  INDEX `team_id` (`team_id` ASC),
  INDEX `status_id` (`status_id` ASC),
  INDEX `created` (`created` ASC),
  INDEX `closed` (`closed` ASC),
  INDEX `duedate` (`duedate` ASC),
  INDEX `topic_id` (`topic_id` ASC),
  INDEX `sla_id` (`sla_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket__cdata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket__cdata` (
  `ticket_id` INT(11) UNSIGNED NOT NULL,
  `subject` MEDIUMTEXT NULL DEFAULT NULL,
  `priority` MEDIUMTEXT NULL DEFAULT NULL,
  `impact` MEDIUMTEXT NULL DEFAULT NULL,
  `urgency` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`ticket_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket_impact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket_impact` (
  `impact_id` TINYINT(4) NOT NULL,
  `impact` VARCHAR(45) NULL DEFAULT NULL,
  `impact_desc` VARCHAR(45) NULL DEFAULT NULL,
  `impact_color` VARCHAR(45) NULL DEFAULT NULL,
  `impact_level` VARCHAR(45) NULL DEFAULT NULL,
  `ispublic` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`impact_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket_priority`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket_priority` (
  `priority_id` TINYINT(4) NOT NULL AUTO_INCREMENT,
  `priority` VARCHAR(60) NOT NULL DEFAULT '',
  `priority_desc` VARCHAR(30) NOT NULL DEFAULT '',
  `priority_color` VARCHAR(7) NOT NULL DEFAULT '',
  `priority_urgency` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `ispublic` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`priority_id`),
  UNIQUE INDEX `priority` (`priority` ASC),
  INDEX `priority_urgency` (`priority_urgency` ASC),
  INDEX `ispublic` (`ispublic` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket_status` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(60) NOT NULL DEFAULT '',
  `state` VARCHAR(16) NULL DEFAULT NULL,
  `mode` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `flags` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `sort` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `properties` TEXT NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name` (`name` ASC),
  INDEX `state` (`state` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 11
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_ticket_urgency`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_ticket_urgency` (
  `urgency_id` TINYINT(4) NOT NULL,
  `urgency` VARCHAR(45) NULL DEFAULT NULL,
  `urgency_desc` VARCHAR(45) NULL DEFAULT NULL,
  `urgency_color` VARCHAR(45) NULL DEFAULT NULL,
  `urgency_level` VARCHAR(45) NULL DEFAULT NULL,
  `ispublic` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`urgency_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_translation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_translation` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object_hash` CHAR(16) CHARACTER SET 'ascii' NULL DEFAULT NULL,
  `type` ENUM('phrase', 'article', 'override') NULL DEFAULT NULL,
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `revision` INT(11) UNSIGNED NULL DEFAULT NULL,
  `agent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `lang` VARCHAR(16) NOT NULL DEFAULT '',
  `text` MEDIUMTEXT NOT NULL,
  `source_text` TEXT NULL DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `type` (`type` ASC, `lang` ASC),
  INDEX `object_hash` (`object_hash` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_user` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `org_id` INT(10) UNSIGNED NOT NULL,
  `default_email_id` INT(10) NOT NULL,
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `name` VARCHAR(128) NOT NULL,
  `created` DATETIME NOT NULL,
  `updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `org_id` (`org_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_user__cdata`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_user__cdata` (
  `user_id` INT(11) UNSIGNED NOT NULL,
  `email` MEDIUMTEXT NULL DEFAULT NULL,
  `name` MEDIUMTEXT NULL DEFAULT NULL,
  `phone` MEDIUMTEXT NULL DEFAULT NULL,
  `notes` MEDIUMTEXT NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_user_account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_user_account` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `status` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `timezone` VARCHAR(64) NULL DEFAULT NULL,
  `lang` VARCHAR(16) NULL DEFAULT NULL,
  `username` VARCHAR(64) NULL DEFAULT NULL,
  `passwd` VARCHAR(128) CHARACTER SET 'ascii' NULL DEFAULT NULL,
  `backend` VARCHAR(32) NULL DEFAULT NULL,
  `extra` TEXT NULL DEFAULT NULL,
  `registered` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `username` (`username` ASC),
  INDEX `user_id` (`user_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `asm`.`asm_user_email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `asm`.`asm_user_email` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `flags` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `address` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `address` (`address` ASC),
  INDEX `user_email_lookup` (`user_id` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
