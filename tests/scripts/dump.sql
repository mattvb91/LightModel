USE mysql;
UPDATE user SET password = PASSWORD('') WHERE user = 'root';
FLUSH PRIVILEGES;

CREATE DATABASE test;
USE test;

CREATE TABLE `user` (
  `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(45)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC)
);

CREATE TABLE `event` (
  `event_id` VARCHAR(45) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `date` DATETIME NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`event_id`),
  UNIQUE INDEX `event_id_UNIQUE` (`event_id` ASC));

CREATE TABLE `books` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `fk_books_user_idx` (`user_id` ASC),
  CONSTRAINT `fk_books_user`
  FOREIGN KEY (`user_id`)
  REFERENCES `user` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);

INSERT INTO `user` (`id`, `username`) VALUES ('1', 'Test User');
INSERT INTO `event` (`event_id`, `name`, `date`, `description`) VALUES ('test_event', 'Test Event', '2000-01-01 20:00:01', 'This is a test description');