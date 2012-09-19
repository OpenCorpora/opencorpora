SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `books` (
    `book_id`   MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `book_name` VARCHAR(255) NOT NULL,
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX (`parent_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `book_tags` (
    `book_id`   MEDIUMINT UNSIGNED NOT NULL,
    `tag_name`  VARCHAR(512) NOT NULL,
    INDEX (`book_id`),
    INDEX (`tag_name`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sources` (
    `source_id` MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id` MEDIUMINT UNSIGNED NOT NULL,
    `url`       VARCHAR(512) NOT NULL,
    `title`     VARCHAR(100) NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `book_id`   MEDIUMINT UNSIGNED NOT NULL,
    INDEX(`user_id`),
    INDEX(`book_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sources_comments` (
    `comment_id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `source_id`  MEDIUMINT UNSIGNED NOT NULL,
    `user_id`    SMALLINT UNSIGNED NOT NULL,
    `text`       TEXT NOT NULL,
    `timestamp`  INT UNSIGNED NOT NULL,
    INDEX(`source_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sources_status` (
    `source_id` MEDIUMINT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `status`    TINYINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    INDEX(`source_id`),
    INDEX(`status`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_pools` (
    `pool_id`      SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `pool_name`    VARCHAR(120) NOT NULL,
    `grammemes`    VARCHAR(120) NOT NULL,
    `gram_descr`   VARCHAR(255) NOT NULL,
    `token_check`  TINYINT UNSIGNED NOT NULL,
    `users_needed` TINYINT UNSIGNED NOT NULL,
    `created_ts`   INT UNSIGNED NOT NULL,
    `updated_ts`   INT UNSIGNED NOT NULL,
    `author_id`    SMALLINT UNSIGNED NOT NULL,
    `moderator_id` SMALLINT UNSIGNED NOT NULL,
    `status`       TINYINT UNSIGNED NOT NULL,
    `revision`     INT UNSIGNED NOT NULL,
    `comment`      TEXT NOT NULL,
    INDEX(`status`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_candidate_samples` (
    `pool_id` SMALLINT UNSIGNED NOT NULL,
    `tf_id`   INT UNSIGNED NOT NULL,
    INDEX(`pool_id`),
    INDEX(`tf_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_samples` (
    `sample_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `pool_id`   SMALLINT UNSIGNED NOT NULL,
    `tf_id`     INT UNSIGNED NOT NULL,
    INDEX(`pool_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_moderated_samples` (
    `sample_id` INT UNSIGNED NOT NULL PRIMARY KEY,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `answer`    TINYINT UNSIGNED NOT NULL,
    `status`    TINYINT UNSIGNED NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_instances` (
    `instance_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `sample_id`   INT UNSIGNED NOT NULL,
    `user_id`     SMALLINT UNSIGNED NOT NULL,
    `ts_finish`   INT UNSIGNED NOT NULL,
    `answer`      TINYINT UNSIGNED NOT NULL,
    INDEX(`sample_id`),
    INDEX(`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_rejected_samples` (
    `sample_id` INT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    INDEX(`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_comments` (
    `comment_id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `sample_id`  INT UNSIGNED NOT NULL,
    `user_id`    SMALLINT UNSIGNED NOT NULL,
    `text`       TEXT NOT NULL,
    `timestamp`  INT UNSIGNED NOT NULL,
    INDEX(`sample_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `morph_annot_click_log` (
    `sample_id` INT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    `clck_type` TINYINT UNSIGNED NOT NULL,
    INDEX(`user_id`),
    INDEX(`timestamp`)
)  ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `downloaded_urls` (
    `url`      VARCHAR(512) NOT NULL,
    `filename` VARCHAR(100) NOT NULL,
    INDEX(`url`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `paragraphs` (
    `par_id`       SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `book_id`      MEDIUMINT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    INDEX (`book_id`),
    INDEX (`pos`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sentences` (
    `sent_id`      MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `par_id`       SMALLINT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    `source`       TEXT NOT NULL,
    `check_status` SMALLINT UNSIGNED NOT NULL,
    INDEX (`par_id`),
    INDEX (`pos`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sentence_check` (
    `sent_id`   MEDIUMINT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `status`    TINYINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    INDEX(`sent_id`),
    INDEX(`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sentence_comments` (
    `comment_id` SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id`  SMALLINT UNSIGNED NOT NULL,
    `sent_id`    MEDIUMINT UNSIGNED NOT NULL,
    `user_id`    SMALLINT UNSIGNED NOT NULL,
    `text`       TEXT NOT NULL,
    `timestamp`  INT UNSIGNED NOT NULL,
    INDEX(`sent_id`),
    INDEX(`parent_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sentence_authors` (
    `sent_id`   MEDIUMINT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    INDEX(`sent_id`),
    INDEX(`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `text_forms` (
    `tf_id`        INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `sent_id`      MEDIUMINT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    `tf_text`      VARCHAR(100) NOT NULL,
    `dict_updated` TINYINT UNSIGNED NOT NULL,
    INDEX (`sent_id`),
    INDEX (`pos`),
    INDEX (`dict_updated`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `users` (
    `user_id`         SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user_name`       VARCHAR(120) NOT NULL,
    `user_passwd`     VARCHAR(32) NOT NULL,
    `user_email`      VARCHAR(100) NOT NULL,
    `user_reg`        INT UNSIGNED NOT NULL,
    `user_shown_name` VARCHAR(120) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `user_aliases` (
    `primary_uid` SMALLINT UNSIGNED NOT NULL,
    `alias_uid`   SMALLINT UNSIGNED NOT NULL UNIQUE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `user_id`           SMALLINT UNSIGNED NOT NULL,
    `perm_admin`        TINYINT UNSIGNED NOT NULL,
    `perm_adder`        TINYINT UNSIGNED NOT NULL,
    `perm_dict`         TINYINT UNSIGNED NOT NULL,
    `perm_disamb`       TINYINT UNSIGNED NOT NULL,
    `perm_check_tokens` TINYINT UNSIGNED NOT NULL,
    `perm_check_morph`  TINYINT UNSIGNED NOT NULL,
    INDEX (`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `user_options_values` (
    `user_id`      SMALLINT UNSIGNED NOT NULL,
    `option_id`    SMALLINT NOT NULL,
    `option_value` SMALLINT NOT NULL,
    INDEX (`user_id`)
) ENGINE = INNODB;

DROP TABLE IF EXISTS `user_options`;
CREATE TABLE `user_options` (
    `option_id`     SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    `option_name`   VARCHAR(128) NOT NULL,
    `option_values` VARCHAR(64) NOT NULL,
    `default_value` SMALLINT NOT NULL,
    `order_by`      SMALLINT UNSIGNED NOT NULL
) ENGINE = INNODB;
INSERT INTO `user_options` VALUES
    ('1', 'Показывать русские названия граммем', '1', '1', '1'),
    ('2', 'Язык/Language', '1=Русский|2=English', '1', '2'),
    ('3', 'Количество примеров для разметки', '1=5|2=10|3=20|4=50', '1', '3');

CREATE TABLE IF NOT EXISTS `user_stats` (
    `user_id`     SMALLINT UNSIGNED NOT NULL,
    `timestamp`   INT UNSIGNED NOT NULL,
    `param_id`    SMALLINT UNSIGNED NOT NULL,
    `param_value` INT UNSIGNED NOT NULL,
    INDEX(`user_id`),
    INDEX(`param_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `user_tokens` (
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `token`     INT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    INDEX(`user_id`)
) ENGINE = INNODB;

DROP TABLE IF EXISTS `user_badges_types`;
CREATE TABLE `user_badges_types` (
    `badge_id`    TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `badge_name`  VARCHAR(127) NOT NULL,
    `badge_descr` TEXT NOT NULL,
    `badge_image` VARCHAR(255) NOT NULL
) ENGINE = INNODB;
INSERT INTO `user_badges_types` VALUES
    (1, 'First ten', 'За 10 ответов', ''),
    (2, 'First fifty', 'За 50 ответов', ''),
    (3, 'First hundred', 'За 100 ответов', ''),
    (4, 'Two hundred', 'За 200 ответов', ''),
    (5, 'Half thousand', 'За 500 ответов', ''),
    (6, 'Thousand', 'За 1000 ответов', '');

CREATE TABLE IF NOT EXISTS `user_badges` (
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `badge_id`  TINYINT UNSIGNED NOT NULL,
    `shown` INT UNSIGNED NOT NULL,
    INDEX(`user_id`),
    INDEX(`shown`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tf_revisions` (
    `rev_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`   INT UNSIGNED NOT NULL,
    `tf_id`    INT UNSIGNED NOT NULL,
    `rev_text` TEXT NOT NULL,
    INDEX (`set_id`),
    INDEX (`tf_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `rev_sets` (
    `set_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `timestamp` INT UNSIGNED NOT NULL,
    `user_id`   SMALLINT UNSIGNED NOT NULL,
    `comment`   TEXT NOT NULL,
    INDEX (`timestamp`),
    INDEX (`user_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_lemmata` (
    `lemma_id`    MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_text`  VARCHAR(50) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_lemmata_deleted` (
    `lemma_id`    MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY,
    `lemma_text`  VARCHAR(50) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_links_types` (
    `link_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `link_name` VARCHAR(50) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_links` (
    `link_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma1_id` MEDIUMINT UNSIGNED NOT NULL,
    `lemma2_id` MEDIUMINT UNSIGNED NOT NULL,
    `link_type` SMALLINT UNSIGNED NOT NULL,
    INDEX (`lemma1_id`),
    INDEX (`lemma2_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_links_revisions` (
    `rev_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`    INT UNSIGNED NOT NULL,
    `lemma1_id` MEDIUMINT UNSIGNED NOT NULL,
    `lemma2_id` MEDIUMINT UNSIGNED NOT NULL,
    `link_type` SMALLINT UNSIGNED NOT NULL,
    `action`    TINYINT UNSIGNED NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_lex` (
    `lex_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_id`  MEDIUMINT UNSIGNED NOT NULL,
    `lex_descr` TEXT NOT NULL,
    INDEX (`lemma_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_revisions` (
    `rev_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`     INT UNSIGNED NOT NULL,
    `lemma_id`   MEDIUMINT UNSIGNED NOT NULL,
    `rev_text`   TEXT NOT NULL,
    `f2l_check`  TINYINT(1) UNSIGNED NOT NULL,
    `dict_check` TINYINT(1) UNSIGNED NOT NULL,
    INDEX (`set_id`),
    INDEX (`lemma_id`),
    INDEX (`f2l_check`),
    INDEX (`dict_check`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_errata` (
    `error_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `timestamp`   INT UNSIGNED NOT NULL,
    `rev_id`      INT UNSIGNED NOT NULL,
    `error_type`  SMALLINT UNSIGNED NOT NULL,
    `error_descr` TEXT NOT NULL,
    INDEX (`error_type`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `dict_errata_exceptions` (
    `item_id`     SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `error_type`  SMALLINT UNSIGNED NOT NULL,
    `error_descr` TEXT NOT NULL,
    `author_id`   INT UNSIGNED NOT NULL,
    `timestamp`   INT UNSIGNED NOT NULL,
    `comment`     TEXT NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `updated_forms` (
    `form_text` VARCHAR(50) NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `form2lemma` (
    `form_text`  VARCHAR(50) NOT NULL,
    `lemma_id`   MEDIUMINT UNSIGNED NOT NULL,
    `lemma_text` VARCHAR(50) NOT NULL,
    `grammems`   TEXT NOT NULL,
    INDEX (`form_text`),
    INDEX (`lemma_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `form2tf` (
    `form_text` VARCHAR(50) NOT NULL,
    `tf_id`     INT UNSIGNED NOT NULL,
    INDEX (`form_text`),
    INDEX (`tf_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `gram` (
    `gram_id`    TINYINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id`  TINYINT UNSIGNED NOT NULL,
    `inner_id`   VARCHAR(20) NOT NULL,
    `outer_id`   VARCHAR(20) NOT NULL,
    `gram_descr` VARCHAR(50) NOT NULL,
    `orderby`    SMALLINT NOT NULL,
    INDEX (`parent_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `gram_restrictions` (
    `restr_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    `if_id`      TINYINT UNSIGNED NOT NULL,
    `then_id`    TINYINT UNSIGNED NOT NULL,
    `restr_type` TINYINT(1) UNSIGNED NOT NULL,
    `obj_type`   TINYINT(1) UNSIGNED NOT NULL,
    `auto`       TINYINT(1) UNSIGNED NOT NULL
) ENGINE = INNODB;

DROP TABLE IF EXISTS `stats_param`;
CREATE TABLE `stats_param` (
    `param_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    `param_name` VARCHAR(32) NOT NULL,
    `is_active`  TINYINT(1) UNSIGNED NOT NULL
) ENGINE = INNODB;
INSERT INTO `stats_param` VALUES
    ('1',  'total_books', '1'),
    ('2',  'total_sentences', '1'),
    ('3',  'total_tokens', '1'),
    ('4',  'total_lemmata', '1'),
    ('5',  'total_words', '1'),
    ('6',  'added_sentences', '1'),
    ('7',  'tokenizer_confidence', '1'),
    ('8',  'chaskor_books', '1'),
    ('9',  'chaskor_sentences', '1'),
    ('10', 'chaskor_tokens', '1'),
    ('11', 'chaskor_words', '1'),
    ('12', 'wikinews_books', '1'),
    ('13', 'wikinews_sentences', '1'),
    ('14', 'wikinews_tokens', '1'),
    ('15', 'wikinews_words', '1'),
    ('16', 'wikipedia_books', '1'),
    ('17', 'wikipedia_sentences', '1'),
    ('18', 'wikipedia_tokens', '1'),
    ('19', 'wikipedia_words', '1'),
    ('20', 'blogs_books', '1'),
    ('21', 'blogs_sentences', '1'),
    ('22', 'blogs_tokens', '1'),
    ('23', 'blogs_words', '1'),
    ('24', 'chaskor_news_books', '1'),
    ('25', 'chaskor_news_sentences', '1'),
    ('26', 'chaskor_news_tokens', '1'),
    ('27', 'chaskor_news_words', '1'),
    ('28', 'tokenizer_broken_token_id', '1'),
    ('29', 'fiction_books', '1'),
    ('30', 'fiction_sentences', '1'),
    ('31', 'fiction_tokens', '1'),
    ('32', 'fiction_words', '1'),
    ('33', 'annotators', '1'),
    ('34', 'annotators_divergence', '0');

CREATE TABLE IF NOT EXISTS `stats_values` (
    `timestamp`   INT UNSIGNED NOT NULL,
    `param_id`    SMALLINT UNSIGNED NOT NULL,
    `param_value` INT UNSIGNED NOT NULL,
    INDEX(`param_id`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tag_stats` (
    `prefix` VARCHAR(16) NOT NULL,
    `value`  VARCHAR(500) NOT NULL,
    `texts`  SMALLINT UNSIGNED NOT NULL,
    `words`  INT UNSIGNED NOT NULL,
    INDEX(`prefix`),
    INDEX(`texts`),
    INDEX(`words`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tokenizer_coeff` (
    `vector` INT UNSIGNED NOT NULL PRIMARY KEY,
    `coeff`  FLOAT NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tokenizer_strange` (
    `sent_id` MEDIUMINT UNSIGNED NOT NULL,
    `pos`     SMALLINT UNSIGNED NOT NULL,
    `border`  TINYINT(1) UNSIGNED NOT NULL,
    `coeff`   FLOAT NOT NULL,
    INDEX(`coeff`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `sentences_strange` (
    `sent_id` MEDIUMINT UNSIGNED NOT NULL
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tag_errors` (
    `book_id`    MEDIUMINT UNSIGNED NOT NULL,
    `tag_name`   VARCHAR(512) NOT NULL,
    `error_type` TINYINT UNSIGNED NOT NULL,
    INDEX(`book_id`),
    INDEX(`error_type`)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `tokenizer_qa` (
  `run` date NOT NULL,
  `threshold` float unsigned NOT NULL DEFAULT '0',
  `precision` float unsigned NOT NULL DEFAULT '0',
  `recall` float unsigned NOT NULL DEFAULT '0',
  `F1` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`run`,`threshold`)
) ENGINE=INNODB;
