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
    `user_name`   VARCHAR(50) NOT NULL,
    `user_passwd` VARCHAR(32) NOT NULL,
    `user_group`  SMALLINT UNSIGNED NOT NULL,
    `user_email`  VARCHAR(100) NOT NULL,
    `user_reg`    INT UNSIGNED NOT NULL
);

CREATE TABLE IF NOT EXISTS `user_options` (
    `user_id`      INT UNSIGNED NOT NULL,
    `option_id`    SMALLINT NOT NULL,
    `option_value` VARCHAR(32) NOT NULL,
    INDEX (`user_id`)
);

CREATE TABLE IF NOT EXISTS `user_options_types` (
    `option_id`     SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `option_name`   VARCHAR(50),
    `option_values` VARCHAR(64),
    `order_by`      SMALLINT UNSIGNED NOT NULL
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
    `restr_id`  SMALLINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, 
    `if1_id`    INT UNSIGNED NOT NULL,
    `if2_id`    INT UNSIGNED NOT NULL,
    `then_id`   INT UNSIGNED NOT NULL,
    `object`    TINYINT(1) UNSIGNED NOT NULL,
    `necessary` TINYINT(1) UNSIGNED NOT NULL,
    `auto`      TINYINT(1) UNSIGNED NOT NULL
);
