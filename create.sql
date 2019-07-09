/* Use UTF8MB4 instead of UTF8, see for info: https://www.teesche.com/blog/utf8mb4_fun_with_mysql */

CREATE TABLE `familyplanner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `entered` datetime DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duedate` datetime NOT NULL,
  `interval` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;