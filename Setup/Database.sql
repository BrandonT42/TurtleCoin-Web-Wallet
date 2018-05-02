SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `address` text NOT NULL,
  `createdat` text,
  `lastlogin` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1 COMMENT='User database';
INSERT INTO `users` (`id`, `username`, `password`, `address`, `createdat`, `lastlogin`) VALUES
(1, 'username', 'empty', 'empty', NULL, NULL);
COMMIT;
