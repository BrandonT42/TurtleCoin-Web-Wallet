SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Name` text NOT NULL COMMENT 'Person''s name',
  `username` text NOT NULL,
  `password` text NOT NULL,
  `uid` text NOT NULL COMMENT 'For password recovery and one-time wallets',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `address` text NOT NULL,
  `createdat` text,
  `lastlogin` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=latin1 COMMENT='User database';
INSERT INTO `users` (`id`, `Name`, `username`, `password`, `uid`, `verified`, `address`, `createdat`, `lastlogin`) VALUES
(1, '', 'Username', 'empty', 'empty', 1, 'empty', NULL, NULL);
COMMIT;
