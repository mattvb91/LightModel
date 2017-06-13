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

INSERT INTO `user` (`id`, `username`) VALUES ('1', 'Test User');