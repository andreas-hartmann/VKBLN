CREATE DATABASE `vocabulary` /*!40100 DEFAULT CHARACTER SET utf8mb3 */ /*!80016 DEFAULT ENCRYPTION='N' */;

CREATE TABLE `vocabulary` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `word` mediumtext NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ts_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `language` varchar(3) NOT NULL DEFAULT 'deu',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `original` int unsigned NOT NULL,
  `translation` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `translation_FK` (`original`),
  KEY `translation_FK_1` (`translation`),
  CONSTRAINT `translation_FK` FOREIGN KEY (`original`) REFERENCES `vocabulary` (`id`),
  CONSTRAINT `translation_FK_1` FOREIGN KEY (`translation`) REFERENCES `vocabulary` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb3;

CREATE DEFINER=`user`@`%` PROCEDURE `vocabulary`.`generate_reverse_translations`()
BEGIN
	INSERT IGNORE INTO translation (original, translation)
	SELECT t.translation, t.original
	FROM translation t
	LEFT JOIN translation t2 ON t.original = t2.translation AND t.translation = t2.original AND t.id < t2.id
	JOIN vocabulary w1 ON t.original = w1.id
	JOIN vocabulary w2 ON t.translation = w2.id
	WHERE t2.id IS NULL;

	DELETE t1
	FROM translation t1, translation t2
	WHERE t1.id < t2.id
	AND t1.original = t2.original
	AND t1.translation = t2.translation;

	COMMIT;
END;

CREATE PROCEDURE insert_translation_pair(
    IN original_word VARCHAR(255),
    IN original_language VARCHAR(255),
    IN translated_word VARCHAR(255),
    IN translated_language VARCHAR(255)
)
BEGIN
    DECLARE original_id, translated_id INT;
    
    INSERT INTO vocabulary (word, language) VALUES (original_word, original_language);
    
    SET original_id = LAST_INSERT_ID();
    
    INSERT INTO vocabulary (word, language) VALUES (translated_word, translated_language);
    
    SET translated_id = LAST_INSERT_ID();
    
    INSERT INTO translation (original, translation) VALUES (original_id, translated_id);
    INSERT INTO translation (original, translation) VALUES (translated_id, original_id);
END;
