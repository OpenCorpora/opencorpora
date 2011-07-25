SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `books` (
    `book_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `book_name` VARCHAR(100) NOT NULL,
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX (`parent_id`)
);

CREATE TABLE IF NOT EXISTS `book_tags` (
    `book_id`   INT UNSIGNED NOT NULL,
    `tag_name`  VARCHAR(512) NOT NULL,
    INDEX (`book_id`),
    INDEX (`tag_name`)
);

CREATE TABLE IF NOT EXISTS `sources` (
    `source_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id` INT UNSIGNED NOT NULL,
    `url`       VARCHAR(512) NOT NULL,
    `title`     VARCHAR(100) NOT NULL,
    `user_id`   INT UNSIGNED NOT NULL,
    `book_id`   INT UNSIGNED NOT NULL,
    INDEX(`user_id`),
    INDEX(`book_id`)
);

CREATE TABLE IF NOT EXISTS `sources_comments` (
    `comment_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `source_id`  INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `text`       TEXT NOT NULL,
    `timestamp`  INT UNSIGNED NOT NULL,
    INDEX(`source_id`)
);

CREATE TABLE IF NOT EXISTS `sources_status` (
    `source_id` INT UNSIGNED NOT NULL,
    `user_id`   INT UNSIGNED NOT NULL,
    `status`    TINYINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL,
    INDEX(`source_id`),
    INDEX(`status`)
);

CREATE TABLE IF NOT EXISTS `downloaded_urls` (
    `url`      VARCHAR(512) NOT NULL,
    `filename` VARCHAR(100) NOT NULL,
    INDEX(`url`)
);

CREATE TABLE IF NOT EXISTS `paragraphs` (
    `par_id`       INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `book_id`      INT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    INDEX (`book_id`),
    INDEX (`pos`)
);

CREATE TABLE IF NOT EXISTS `sentences` (
    `sent_id`      INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `par_id`       INT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    `source`       TEXT NOT NULL,
    `check_status` SMALLINT UNSIGNED NOT NULL,
    INDEX (`par_id`),
    INDEX (`pos`)
);

CREATE TABLE IF NOT EXISTS `sentence_check` (
    `sent_id`   INT UNSIGNED NOT NULL,
    `user_id`   INT UNSIGNED NOT NULL,
    `status`    TINYINT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `sentence_comments` (
    `comment_id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id`  INT UNSIGNED NOT NULL,
    `sent_id`    INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `text`       TEXT NOT NULL,
    `timestamp`  INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `text_forms` (
    `tf_id`        INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `sent_id`      INT UNSIGNED NOT NULL,
    `pos`          SMALLINT UNSIGNED NOT NULL,
    `tf_text`      VARCHAR(100) NOT NULL,
    `dict_updated` TINYINT UNSIGNED NOT NULL,
    INDEX (`sent_id`),
    INDEX (`pos`),
    INDEX (`dict_updated`)
);

CREATE TABLE IF NOT EXISTS `users` (
    `user_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user_name`   VARCHAR(120) NOT NULL,
    `user_passwd` VARCHAR(32) NOT NULL,
    `user_email`  VARCHAR(100) NOT NULL,
    `user_reg`    INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `user_id`           INT UNSIGNED NOT NULL,
    `perm_admin`        TINYINT UNSIGNED NOT NULL,
    `perm_adder`        TINYINT UNSIGNED NOT NULL,
    `perm_dict`         TINYINT UNSIGNED NOT NULL,
    `perm_disamb`       TINYINT UNSIGNED NOT NULL,
    `perm_check_tokens` TINYINT UNSIGNED NOT NULL,
    `perm_check_morph`  TINYINT UNSIGNED NOT NULL,
    INDEX (`user_id`)
);

CREATE TABLE IF NOT EXISTS `user_options_values` (
    `user_id`      INT UNSIGNED NOT NULL,
    `option_id`    SMALLINT NOT NULL,
    `option_value` SMALLINT NOT NULL,
    INDEX (`user_id`)
);

DROP TABLE IF EXISTS `user_options`;
CREATE TABLE `user_options` (
    `option_id`     SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    `option_name`   VARCHAR(128),
    `option_values` VARCHAR(64),
    `default_value` SMALLINT NOT NULL,
    `order_by`      SMALLINT UNSIGNED NOT NULL
);
INSERT INTO `user_options` VALUES
    ('1', 'Показывать русские названия граммем', '1', '1', '1'),
    ('2', 'Язык/Language', '1=Русский|2=English', '1', '2');

CREATE TABLE IF NOT EXISTS `user_stats` (
    `user_id`     INT UNSIGNED NOT NULL,
    `timestamp`   INT UNSIGNED NOT NULL,
    `param_id`    SMALLINT UNSIGNED NOT NULL,
    `param_value` INT UNSIGNED NOT NULL,
    INDEX(`user_id`)
);

CREATE TABLE IF NOT EXISTS `user_tokens` (
    `user_id`   INT UNSIGNED NOT NULL,
    `token`     INT UNSIGNED NOT NULL,
    `timestamp` INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `tf_revisions` (
    `rev_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`   INT UNSIGNED NOT NULL,
    `tf_id`    INT UNSIGNED NOT NULL,
    `rev_text` TEXT NOT NULL,
    INDEX (`set_id`),
    INDEX (`tf_id`)
);

CREATE TABLE IF NOT EXISTS `rev_sets` (
    `set_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `timestamp` INT UNSIGNED NOT NULL,
    `user_id`   INT UNSIGNED NOT NULL,
    `comment`   TEXT NOT NULL,
    INDEX (`timestamp`),
    INDEX (`user_id`)
);

CREATE TABLE IF NOT EXISTS `dict_lemmata` (
    `lemma_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_text`  VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `dict_lemmata_deleted` (
    `lemma_id`    INT UNSIGNED NOT NULL PRIMARY KEY,
    `lemma_text`  VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `dict_links_types` (
    `link_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `link_name` VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `dict_links` (
    `link_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma1_id` INT UNSIGNED NOT NULL,
    `lemma2_id` INT UNSIGNED NOT NULL,
    `link_type` SMALLINT UNSIGNED NOT NULL,
    INDEX (`lemma1_id`),
    INDEX (`lemma2_id`)
);

CREATE TABLE IF NOT EXISTS `dict_links_revisions` (
    `rev_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`    INT UNSIGNED NOT NULL,
    `lemma1_id` INT UNSIGNED NOT NULL,
    `lemma2_id` INT UNSIGNED NOT NULL,
    `link_type` SMALLINT UNSIGNED NOT NULL,
    `action`    TINYINT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `dict_lex` (
    `lex_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_id`  INT UNSIGNED NOT NULL,
    `lex_descr` TEXT NOT NULL,
    INDEX (`lemma_id`)
);

CREATE TABLE IF NOT EXISTS `dict_revisions` (
    `rev_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`     INT UNSIGNED NOT NULL,
    `lemma_id`   INT UNSIGNED NOT NULL,
    `rev_text`   TEXT NOT NULL,
    `f2l_check`  TINYINT(1) UNSIGNED NOT NULL,
    `dict_check` TINYINT(1) UNSIGNED NOT NULL,
    INDEX (`set_id`),
    INDEX (`lemma_id`),
    INDEX (`f2l_check`),
    INDEX (`dict_check`)
);

CREATE TABLE IF NOT EXISTS `dict_errata` (
    `error_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `timestamp`   INT UNSIGNED NOT NULL,
    `rev_id`      INT UNSIGNED NOT NULL,
    `error_type`  SMALLINT UNSIGNED NOT NULL,
    `error_descr` TEXT NOT NULL,
    INDEX (`error_type`)
);

CREATE TABLE IF NOT EXISTS `dict_errata_exceptions` (
    `item_id`     SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `error_type`  SMALLINT UNSIGNED NOT NULL,
    `error_descr` TEXT NOT NULL,
    `author_id`   INT UNSIGNED NOT NULL,
    `timestamp`   INT UNSIGNED NOT NULL,
    `comment`     TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS `updated_forms` (
    `form_text` VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `form2lemma` (
    `form_text`  VARCHAR(50) NOT NULL,
    `lemma_id`   INT UNSIGNED NOT NULL,
    `lemma_text` VARCHAR(50) NOT NULL,
    `grammems`   TEXT NOT NULL,
    INDEX (`form_text`),
    INDEX (`lemma_id`)
);

CREATE TABLE IF NOT EXISTS `form2tf` (
    `form_text` VARCHAR(50) NOT NULL,
    `tf_id`     INT UNSIGNED NOT NULL,
    INDEX (`form_text`),
    INDEX (`tf_id`)
);

CREATE TABLE IF NOT EXISTS `gram` (
    `gram_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `parent_id`  INT UNSIGNED NOT NULL,
    `inner_id`   VARCHAR(20) NOT NULL,
    `outer_id`   VARCHAR(20) NOT NULL,
    `gram_descr` VARCHAR(50) NOT NULL,
    `orderby`    SMALLINT NOT NULL,
    INDEX (`parent_id`)
);

CREATE TABLE IF NOT EXISTS `gram_restrictions` (
    `restr_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    `if_id`      INT UNSIGNED NOT NULL,
    `then_id`    INT UNSIGNED NOT NULL,
    `restr_type` TINYINT(1) UNSIGNED NOT NULL,
    `obj_type`   TINYINT(1) UNSIGNED NOT NULL,
    `auto`       TINYINT(1) UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS `stats_param`;
CREATE TABLE `stats_param` (
    `param_id`   SMALLINT UNSIGNED NOT NULL PRIMARY KEY,
    `param_name` VARCHAR(32) NOT NULL,
    `is_active`  TINYINT(1) UNSIGNED NOT NULL
);
INSERT INTO `stats_param` VALUES
    ('1', 'total_books', '1'),
    ('2', 'total_sentences', '1'),
    ('3', 'total_tokens', '1'),
    ('4', 'total_lemmata', '1'),
    ('5', 'total_words', '1'),
    ('6', 'added_sentences', '1'),
    ('7', 'tokenizer_confidence', '1');

CREATE TABLE IF NOT EXISTS `stats_values` (
    `timestamp`   INT UNSIGNED NOT NULL,
    `param_id`    SMALLINT UNSIGNED NOT NULL,
    `param_value` INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `tokenizer_coeff` (
    `vector` INT UNSIGNED NOT NULL,
    `coeff`  FLOAT NOT NULL
);

CREATE TABLE IF NOT EXISTS `tokenizer_strange` (
    `sent_id` INT UNSIGNED NOT NULL,
    `pos`     SMALLINT UNSIGNED NOT NULL,
    `border`  TINYINT(1) UNSIGNED NOT NULL,
    `coeff`   FLOAT NOT NULL
);
