CREATE TABLE IF NOT EXISTS `books` (
    `book_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `book_name` VARCHAR(100) NOT NULL,
    `parent_id` INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX (`parent_id`)
);

CREATE TABLE IF NOT EXISTS `book_tags` (
    `book_id`   INT UNSIGNED NOT NULL,
    `tag_name`  VARCHAR(255) NOT NULL,
    INDEX (`book_id`),
    INDEX (`tag_name`)
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
    `check_status` SMALLINT UNSIGNED NOT NULL,
    INDEX (`par_id`),
    INDEX (`pos`)
);

CREATE TABLE IF NOT EXISTS `text_forms` (
    `tf_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `sent_id` INT UNSIGNED NOT NULL,
    `pos`     SMALLINT UNSIGNED NOT NULL,
    `tf_text` VARCHAR(100) NOT NULL,
    INDEX (`sent_id`),
    INDEX (`pos`)
);

CREATE TABLE IF NOT EXISTS `users` (
    `user_id`     INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `user_name`   VARCHAR(50) NOT NULL,
    `user_passwd` VARCHAR(32) NOT NULL,
    `user_group`  SMALLINT UNSIGNED NOT NULL,
    `user_email`  VARCHAR(100) NOT NULL,
    `user_reg`    INT UNSIGNED NOT NULL
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
    INDEX (`timestamp`),
    INDEX (`user_id`)
);

CREATE TABLE IF NOT EXISTS `dict_lemmata` (
    `lemma_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_text`  VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS `dict_lex` (
    `lex_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `lemma_id`  INT UNSIGNED NOT NULL,
    `lex_descr` TEXT NOT NULL,
    INDEX (`lemma_id`)
);

CREATE TABLE IF NOT EXISTS `dict_revisions` (
    `rev_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `set_id`    INT UNSIGNED NOT NULL,
    `lemma_id`  INT UNSIGNED NOT NULL,
    `rev_text`  TEXT NOT NULL,
    `f2l_check` TINYINT(1) UNSIGNED NOT NULL,
    INDEX (`set_id`),
    INDEX (`lemma_id`)
);

CREATE TABLE IF NOT EXISTS `form2lemma` (
    `form_text`  VARCHAR(50) NOT NULL,
    `lemma_id`   INT UNSIGNED NOT NULL,
    `lemma_text` VARCHAR(50) NOT NULL,
    `grammems`   TEXT NOT NULL,
    INDEX (`form_text`),
    INDEX (`lemma_id`)
);

CREATE TABLE IF NOT EXISTS `gram_types` (
    `type_id`   INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `type_name` VARCHAR(30) NOT NULL,
    `orderby`   SMALLINT NOT NULL,
    INDEX (`orderby`)
);

CREATE TABLE IF NOT EXISTS `gram` (
    `gram_id`    INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gram_type`  INT UNSIGNED NOT NULL,
    `aot_id`     VARCHAR(20) NOT NULL,
    `gram_name`  VARCHAR(20) NOT NULL,
    `gram_descr` VARCHAR(50) NOT NULL,
    INDEX (`gram_type`)
);
