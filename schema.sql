CREATE TABLE `pgkeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(45) DEFAULT NULL,
  `pgpkey` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
)

