SET time_zone = "+00:00";
USE fueterpl_entwicklung;

CREATE TABLE IF NOT EXISTS `AKTION` (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `titel` varchar(100) NOT NULL,
                                        `beschreibung` text,
                                        `aktion_info` text,
                                        `sichtbar` int(1) NOT NULL DEFAULT '1',
                                        `gueltig_ab` datetime DEFAULT NULL,
                                        `gueltig_bis` datetime DEFAULT NULL,
                                        `Haupt_bild` int(11) DEFAULT NULL,
                                        PRIMARY KEY (`id`)
) ;