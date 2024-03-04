SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE IF NOT EXISTS `email`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `environment` VARCHAR(4) NOT NULL,
    `sender_name` VARCHAR(255),
    `sender_email` VARCHAR(255) NOT NULL,
    `to` TEXT,
    `cc` TEXT,
    `bcc` TEXT,
    `subject` TEXT NOT NULL,
    `body` LONGTEXT NOT NULL,
    `message` LONGBLOB NOT NULL,
    `attachments` MEDIUMBLOB,
    `priority` INTEGER DEFAULT 0 NOT NULL,
    `sending_at` DATETIME,
    `created_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `email_log`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `sender_name` VARCHAR(255),
    `sender_email` VARCHAR(255) NOT NULL,
    `to` TEXT,
    `cc` TEXT,
    `bcc` TEXT,
    `subject` TEXT NOT NULL,
    `attachments` MEDIUMBLOB,
    `created_at` DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `email_template`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `tid` VARCHAR(100) NOT NULL,
    `email_template_group_id` INTEGER NOT NULL,
    `from` VARCHAR(255),
    `dynamic_to` TINYINT(1) DEFAULT 0 NOT NULL,
    `to` VARCHAR(255),
    `cc` VARCHAR(255),
    `bcc` VARCHAR(255),
    `priority` INTEGER DEFAULT 0 NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_tid` (`tid`),
    INDEX `fi_il_template_FK_1` (`email_template_group_id`),
    CONSTRAINT `email_template_FK_1`
        FOREIGN KEY (`email_template_group_id`)
        REFERENCES `email_template_group` (`id`)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `email_template` (`id`, `tid`, `email_template_group_id`, `from`, `dynamic_to`, `to`, `cc`, `bcc`, `priority`) VALUES
(1, 'Nový účet administrátora', 2,  'automat@fontai.com', 1,  NULL, NULL, NULL, 0),
(2, 'Zapomenuté heslo administrátora',  2,  'automat@fontai.com', 1,  NULL, NULL, NULL, 0)
ON DUPLICATE KEY UPDATE id = id;

CREATE TABLE IF NOT EXISTS `email_template_group`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `priority` INTEGER DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_name` (`name`)
) ENGINE=InnoDB;

INSERT INTO `email_template_group` (`id`, `name`, `priority`) VALUES
(1, 'Zákazník', 0),
(2, 'Backend',  0)
ON DUPLICATE KEY UPDATE id = id;

CREATE TABLE IF NOT EXISTS `email_template_i18n`
(
    `id` INTEGER NOT NULL,
    `culture` VARCHAR(5) DEFAULT 'cs' NOT NULL,
    `body` TEXT NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `from_name` VARCHAR(255),
    PRIMARY KEY (`id`,`culture`),
    CONSTRAINT `email_template_i18n_fk_1ec74f`
        FOREIGN KEY (`id`)
        REFERENCES `email_template` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `email_template_i18n` (`body`, `subject`, `from_name`, `id`, `culture`) VALUES
('<p>Dobr&yacute; den,</p>\r\n\r\n<p>byl V&aacute;m vytvořen nov&yacute; &uacute;čet ke spr&aacute;vě syst&eacute;mu Fontai backend. Přejděte pros&iacute;m na adresu <a href=\"::URL::\">::URL::</a> a vyplňte Va&scaron;e nov&eacute; heslo.</p>\r\n\r\n<p>S pozdravem<br />\r\nt&yacute;m Fontai</p>', 'Nový účet',  'Fontai backend', 1,  'cs'),
('<p>Dobrý den,</p>\r\n\r\n<p>byl Vám vytvořen nový účet ke správě systému Fontai backend. Přejděte prosím na adresu <a href=\"::URL::\">::URL::</a> a vyplňte Vaše nové heslo.</p>\r\n\r\n<p>S pozdravem<br />\r\ntým Fontai</p>\r\n', 'Nový účet',  'Fontai backend', 1,  'en'),
('<p>Hezk&yacute; den,</p>\r\n\r\n<p>bylo pož&aacute;d&aacute;no o změnu hesla Va&scaron;eho &uacute;čtu ke spr&aacute;vě syst&eacute;mu Fontai backend. Přejděte pros&iacute;m na adresu <a href=\"::URL::\">::URL::</a> a vyplňte Va&scaron;e nov&eacute; heslo.</p>',  'Změna hesla',  'Fontai backend', 2,  'cs'),
('<p>Hi,</p>\r\n\r\n<p>someone have requested password change of your Fontai backend project access. Please visit&nbsp;<a href=\"::URL::\">::URL::</a> and fill your new password.</p>\r\n',  'Password change',  'Fontai backend', 2,  'en')
ON DUPLICATE KEY UPDATE id = id;

CREATE TABLE IF NOT EXISTS `email_template_variable`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `email_template_id` INTEGER NOT NULL,
    `variable` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `unique_variable` (`email_template_id`, `variable`),
    CONSTRAINT `email_template_variable_FK_1`
        FOREIGN KEY (`email_template_id`)
        REFERENCES `email_template` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `email_template_variable` (`id`, `email_template_id`, `variable`, `description`) VALUES
(1, 1,  '::NAME::', 'Název backendu'),
(2, 1,  '::URL::',  'URL adresa pro první přihlášení do účtu')
ON DUPLICATE KEY UPDATE id = id;