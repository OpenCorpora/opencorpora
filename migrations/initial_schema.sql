
DROP TABLE IF EXISTS `anaphora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora` (
  `ref_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `token_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `rev_set_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`ref_id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anaphora_syntax_annotators`
--

DROP TABLE IF EXISTS `anaphora_syntax_annotators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora_syntax_annotators` (
  `user_id` smallint(5) unsigned NOT NULL,
  `book_id` mediumint(8) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anaphora_syntax_group_types`
--

DROP TABLE IF EXISTS `anaphora_syntax_group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora_syntax_group_types` (
  `type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anaphora_syntax_groups`
--

DROP TABLE IF EXISTS `anaphora_syntax_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora_syntax_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_type` tinyint(3) unsigned NOT NULL,
  `rev_set_id` int(10) unsigned NOT NULL,
  `head_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `marks` enum('bad','suspicious','no head','all') DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14412 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anaphora_syntax_groups_complex`
--

DROP TABLE IF EXISTS `anaphora_syntax_groups_complex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora_syntax_groups_complex` (
  `parent_gid` int(10) unsigned NOT NULL,
  `child_gid` int(10) unsigned NOT NULL,
  KEY `parent_gid` (`parent_gid`),
  KEY `child_gid` (`child_gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anaphora_syntax_groups_simple`
--

DROP TABLE IF EXISTS `anaphora_syntax_groups_simple`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anaphora_syntax_groups_simple` (
  `group_id` int(10) unsigned NOT NULL,
  `token_id` int(10) unsigned NOT NULL,
  KEY `group_id` (`group_id`),
  KEY `token_id` (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `book_tags`
--

DROP TABLE IF EXISTS `book_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `book_tags` (
  `book_id` mediumint(8) unsigned NOT NULL,
  `tag_name` varchar(512) NOT NULL,
  KEY `book_id` (`book_id`),
  KEY `tag_name` (`tag_name`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `books` (
  `book_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `book_name` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `syntax_on` tinyint(3) unsigned NOT NULL,
  `old_syntax_moder_id` smallint(5) unsigned NOT NULL,
  `ne_on` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`book_id`),
  KEY `parent_id` (`parent_id`),
  KEY `ne_on` (`ne_on`),
  KEY `syntax_on` (`syntax_on`)
) ENGINE=InnoDB AUTO_INCREMENT=3476 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_errata`
--

DROP TABLE IF EXISTS `dict_errata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_errata` (
  `error_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL,
  `rev_id` int(10) unsigned NOT NULL,
  `error_type` smallint(5) unsigned NOT NULL,
  `error_descr` text NOT NULL,
  PRIMARY KEY (`error_id`),
  KEY `error_type` (`error_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3365 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_errata_exceptions`
--

DROP TABLE IF EXISTS `dict_errata_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_errata_exceptions` (
  `item_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `error_type` smallint(5) unsigned NOT NULL,
  `error_descr` text NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_lemmata`
--

DROP TABLE IF EXISTS `dict_lemmata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_lemmata` (
  `lemma_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `lemma_text` varchar(50) NOT NULL,
  PRIMARY KEY (`lemma_id`)
) ENGINE=InnoDB AUTO_INCREMENT=389910 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_lemmata_deleted`
--

DROP TABLE IF EXISTS `dict_lemmata_deleted`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_lemmata_deleted` (
  `lemma_id` mediumint(8) unsigned NOT NULL,
  `lemma_text` varchar(50) NOT NULL,
  PRIMARY KEY (`lemma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_lex`
--

DROP TABLE IF EXISTS `dict_lex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_lex` (
  `lex_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lemma_id` mediumint(8) unsigned NOT NULL,
  `lex_descr` text NOT NULL,
  PRIMARY KEY (`lex_id`),
  KEY `lemma_id` (`lemma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_links`
--

DROP TABLE IF EXISTS `dict_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_links` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lemma1_id` mediumint(8) unsigned NOT NULL,
  `lemma2_id` mediumint(8) unsigned NOT NULL,
  `link_type` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`link_id`),
  KEY `lemma1_id` (`lemma1_id`),
  KEY `lemma2_id` (`lemma2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=268276 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_links_revisions`
--

DROP TABLE IF EXISTS `dict_links_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_links_revisions` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(10) unsigned NOT NULL,
  `lemma1_id` mediumint(8) unsigned NOT NULL,
  `lemma2_id` mediumint(8) unsigned NOT NULL,
  `link_type` smallint(5) unsigned NOT NULL,
  `action` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`rev_id`)
) ENGINE=InnoDB AUTO_INCREMENT=302787 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_links_types`
--

DROP TABLE IF EXISTS `dict_links_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_links_types` (
  `link_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `link_name` varchar(50) NOT NULL,
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dict_revisions`
--

DROP TABLE IF EXISTS `dict_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dict_revisions` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(10) unsigned NOT NULL,
  `lemma_id` mediumint(8) unsigned NOT NULL,
  `rev_text` text NOT NULL,
  `f2l_check` tinyint(1) unsigned NOT NULL,
  `dict_check` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`rev_id`),
  KEY `set_id` (`set_id`),
  KEY `lemma_id` (`lemma_id`),
  KEY `f2l_check` (`f2l_check`),
  KEY `dict_check` (`dict_check`)
) ENGINE=InnoDB AUTO_INCREMENT=393206 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `downloaded_urls`
--

DROP TABLE IF EXISTS `downloaded_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downloaded_urls` (
  `url` varchar(512) NOT NULL,
  `filename` varchar(100) NOT NULL,
  KEY `url` (`url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form2lemma`
--

DROP TABLE IF EXISTS `form2lemma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form2lemma` (
  `form_text` varchar(50) NOT NULL,
  `lemma_id` mediumint(8) unsigned NOT NULL,
  `lemma_text` varchar(50) NOT NULL,
  `grammems` text NOT NULL,
  KEY `form_text` (`form_text`),
  KEY `lemma_id` (`lemma_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form2tf`
--

DROP TABLE IF EXISTS `form2tf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form2tf` (
  `form_text` varchar(50) NOT NULL,
  `tf_id` int(10) unsigned NOT NULL,
  KEY `form_text` (`form_text`),
  KEY `tf_id` (`tf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `good_sentences`
--

DROP TABLE IF EXISTS `good_sentences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `good_sentences` (
  `sent_id` mediumint(8) unsigned NOT NULL,
  `num_words` tinyint(3) unsigned NOT NULL,
  `num_homonymous` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `sent_id` (`sent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gram`
--

DROP TABLE IF EXISTS `gram`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gram` (
  `gram_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` tinyint(3) unsigned NOT NULL,
  `inner_id` varchar(20) NOT NULL,
  `outer_id` varchar(20) NOT NULL,
  `gram_descr` varchar(50) NOT NULL,
  `orderby` smallint(6) NOT NULL,
  PRIMARY KEY (`gram_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gram_restrictions`
--

DROP TABLE IF EXISTS `gram_restrictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gram_restrictions` (
  `restr_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `if_id` tinyint(3) unsigned NOT NULL,
  `then_id` tinyint(3) unsigned NOT NULL,
  `restr_type` tinyint(1) unsigned NOT NULL,
  `obj_type` tinyint(1) unsigned NOT NULL,
  `auto` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`restr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26468 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_candidate_samples`
--

DROP TABLE IF EXISTS `morph_annot_candidate_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_candidate_samples` (
  `pool_id` smallint(5) unsigned NOT NULL,
  `tf_id` int(10) unsigned NOT NULL,
  KEY `pool_id` (`pool_id`),
  KEY `tf_id` (`tf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_click_log`
--

DROP TABLE IF EXISTS `morph_annot_click_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_click_log` (
  `sample_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `clck_type` tinyint(3) unsigned NOT NULL,
  KEY `timestamp` (`timestamp`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_comments`
--

DROP TABLE IF EXISTS `morph_annot_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_comments` (
  `comment_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `text` text NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `sample_id` (`sample_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14883 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_instances`
--

DROP TABLE IF EXISTS `morph_annot_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_instances` (
  `instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `ts_finish` int(10) unsigned NOT NULL,
  `answer` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`instance_id`),
  KEY `sample_id` (`sample_id`),
  KEY `user_id` (`user_id`),
  KEY `answer` (`answer`),
  KEY `ts_finish` (`ts_finish`)
) ENGINE=InnoDB AUTO_INCREMENT=2811661 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_moderated_samples`
--

DROP TABLE IF EXISTS `morph_annot_moderated_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_moderated_samples` (
  `sample_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `answer` tinyint(3) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `manual` tinyint(3) unsigned NOT NULL,
  `merge_status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`sample_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_pool_types`
--

DROP TABLE IF EXISTS `morph_annot_pool_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_pool_types` (
  `type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `grammemes` varchar(120) NOT NULL,
  `gram_descr` varchar(255) NOT NULL,
  `doc_link` text NOT NULL,
  `complexity` tinyint(3) unsigned NOT NULL,
  `has_focus` tinyint(3) unsigned NOT NULL,
  `rating_weight` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_pools`
--

DROP TABLE IF EXISTS `morph_annot_pools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_pools` (
  `pool_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `pool_type` smallint(5) unsigned NOT NULL,
  `pool_name` varchar(120) NOT NULL,
  `token_check` tinyint(3) unsigned NOT NULL,
  `users_needed` tinyint(3) unsigned NOT NULL,
  `created_ts` int(10) unsigned NOT NULL,
  `updated_ts` int(10) unsigned NOT NULL,
  `author_id` smallint(5) unsigned NOT NULL,
  `moderator_id` smallint(5) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `revision` int(10) unsigned NOT NULL,
  PRIMARY KEY (`pool_id`),
  KEY `status` (`status`),
  KEY `pool_type` (`pool_type`)
) ENGINE=InnoDB AUTO_INCREMENT=3683 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_rejected_samples`
--

DROP TABLE IF EXISTS `morph_annot_rejected_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_rejected_samples` (
  `sample_id` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `morph_annot_samples`
--

DROP TABLE IF EXISTS `morph_annot_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `morph_annot_samples` (
  `sample_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pool_id` smallint(5) unsigned NOT NULL,
  `tf_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sample_id`),
  KEY `pool_id` (`pool_id`),
  KEY `tf_id` (`tf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=751148 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ne_entities`
--

DROP TABLE IF EXISTS `ne_entities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ne_entities` (
  `entity_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `annot_id` smallint(5) unsigned NOT NULL,
  `start_token` int(10) unsigned NOT NULL,
  `length` tinyint(3) unsigned NOT NULL,
  `updated_ts` int(10) unsigned NOT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `par_id` (`annot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5620 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ne_entity_tags`
--

DROP TABLE IF EXISTS `ne_entity_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ne_entity_tags` (
  `entity_id` mediumint(8) unsigned NOT NULL,
  `tag_id` tinyint(3) unsigned NOT NULL,
  KEY `entity_id` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ne_paragraphs`
--

DROP TABLE IF EXISTS `ne_paragraphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ne_paragraphs` (
  `annot_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `par_id` smallint(5) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `started_ts` int(10) unsigned NOT NULL,
  `finished_ts` int(10) unsigned NOT NULL,
  PRIMARY KEY (`annot_id`),
  KEY `par_id` (`par_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1600 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ne_tags`
--

DROP TABLE IF EXISTS `ne_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ne_tags` (
  `tag_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(31) NOT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paragraphs`
--

DROP TABLE IF EXISTS `paragraphs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paragraphs` (
  `par_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `book_id` mediumint(8) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`par_id`),
  KEY `book_id` (`book_id`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB AUTO_INCREMENT=33302 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `phinxlog`
--

DROP TABLE IF EXISTS `phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phinxlog` (
  `version` bigint(14) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rev_sets`
--

DROP TABLE IF EXISTS `rev_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rev_sets` (
  `set_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` int(10) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`set_id`),
  KEY `timestamp` (`timestamp`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13377 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentence_authors`
--

DROP TABLE IF EXISTS `sentence_authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_authors` (
  `sent_id` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `sent_id` (`sent_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentence_check`
--

DROP TABLE IF EXISTS `sentence_check`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_check` (
  `sent_id` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `sent_id` (`sent_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentence_comments`
--

DROP TABLE IF EXISTS `sentence_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentence_comments` (
  `comment_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(5) unsigned NOT NULL,
  `sent_id` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `text` text NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `sent_id` (`sent_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentences`
--

DROP TABLE IF EXISTS `sentences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentences` (
  `sent_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `par_id` smallint(5) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  `source` text NOT NULL,
  `check_status` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`sent_id`),
  KEY `par_id` (`par_id`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB AUTO_INCREMENT=93639 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sentences_strange`
--

DROP TABLE IF EXISTS `sentences_strange`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sentences_strange` (
  `sent_id` mediumint(8) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sources`
--

DROP TABLE IF EXISTS `sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources` (
  `source_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL,
  `url` varchar(512) NOT NULL,
  `title` varchar(100) NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `book_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`source_id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24432 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sources_comments`
--

DROP TABLE IF EXISTS `sources_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources_comments` (
  `comment_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `text` text NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sources_status`
--

DROP TABLE IF EXISTS `sources_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sources_status` (
  `source_id` mediumint(8) unsigned NOT NULL,
  `user_id` smallint(5) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `source_id` (`source_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_param`
--

DROP TABLE IF EXISTS `stats_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_param` (
  `param_id` smallint(5) unsigned NOT NULL,
  `param_name` varchar(32) NOT NULL,
  `is_active` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`param_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_values`
--

DROP TABLE IF EXISTS `stats_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_values` (
  `timestamp` int(10) unsigned NOT NULL,
  `param_id` smallint(5) unsigned NOT NULL,
  `param_value` int(10) unsigned NOT NULL,
  KEY `param_id` (`param_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syntax_group_types`
--

DROP TABLE IF EXISTS `syntax_group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syntax_group_types` (
  `type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syntax_groups`
--

DROP TABLE IF EXISTS `syntax_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syntax_groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parse_id` mediumint(8) unsigned NOT NULL,
  `is_complex` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`group_id`),
  KEY `parse_id` (`parse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syntax_groups_revisions`
--

DROP TABLE IF EXISTS `syntax_groups_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syntax_groups_revisions` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `revset_id` int(10) unsigned NOT NULL,
  `group_id` int(10) unsigned NOT NULL,
  `group_type` tinyint(3) unsigned NOT NULL,
  `head_id` int(10) unsigned NOT NULL,
  `rev_text` text NOT NULL,
  `is_last` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`rev_id`),
  KEY `revset_id` (`revset_id`),
  KEY `group_id` (`group_id`),
  KEY `is_last` (`is_last`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syntax_groups_simple`
--

DROP TABLE IF EXISTS `syntax_groups_simple`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syntax_groups_simple` (
  `group_id` int(10) unsigned NOT NULL,
  `token_id` int(10) unsigned NOT NULL,
  KEY `group_id` (`group_id`),
  KEY `token_id` (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `syntax_parses`
--

DROP TABLE IF EXISTS `syntax_parses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `syntax_parses` (
  `parse_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `sent_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`parse_id`),
  KEY `sent_id` (`sent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_errors`
--

DROP TABLE IF EXISTS `tag_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_errors` (
  `book_id` mediumint(8) unsigned NOT NULL,
  `tag_name` varchar(512) NOT NULL,
  `error_type` tinyint(3) unsigned NOT NULL,
  KEY `book_id` (`book_id`),
  KEY `error_type` (`error_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tag_stats`
--

DROP TABLE IF EXISTS `tag_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_stats` (
  `prefix` varchar(16) NOT NULL,
  `value` varchar(500) NOT NULL,
  `texts` smallint(5) unsigned NOT NULL,
  `words` int(10) unsigned NOT NULL,
  KEY `prefix` (`prefix`),
  KEY `texts` (`texts`),
  KEY `words` (`words`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tf_revisions`
--

DROP TABLE IF EXISTS `tf_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tf_revisions` (
  `rev_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(10) unsigned NOT NULL,
  `tf_id` int(10) unsigned NOT NULL,
  `rev_text` text NOT NULL,
  `is_last` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`rev_id`),
  KEY `set_id` (`set_id`),
  KEY `tf_id` (`tf_id`),
  KEY `is_last` (`is_last`)
) ENGINE=InnoDB AUTO_INCREMENT=3700629 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `tokenizer_coeff`
--

DROP TABLE IF EXISTS `tokenizer_coeff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokenizer_coeff` (
  `vector` int(10) unsigned NOT NULL,
  `coeff` float NOT NULL,
  PRIMARY KEY (`vector`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tokenizer_qa`
--

DROP TABLE IF EXISTS `tokenizer_qa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokenizer_qa` (
  `run` date NOT NULL,
  `threshold` float unsigned NOT NULL DEFAULT '0',
  `precision` float unsigned NOT NULL DEFAULT '0',
  `recall` float unsigned NOT NULL DEFAULT '0',
  `F1` float unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`run`,`threshold`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tokenizer_strange`
--

DROP TABLE IF EXISTS `tokenizer_strange`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokenizer_strange` (
  `sent_id` mediumint(8) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  `border` tinyint(1) unsigned NOT NULL,
  `coeff` float NOT NULL,
  KEY `coeff` (`coeff`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens` (
  `tf_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sent_id` mediumint(8) unsigned NOT NULL,
  `pos` smallint(5) unsigned NOT NULL,
  `tf_text` varchar(100) NOT NULL,
  PRIMARY KEY (`tf_id`),
  KEY `sent_id` (`sent_id`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB AUTO_INCREMENT=1699514 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `updated_forms`
--

DROP TABLE IF EXISTS `updated_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `updated_forms` (
  `form_text` varchar(50) NOT NULL,
  `rev_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `updated_tokens`
--

DROP TABLE IF EXISTS `updated_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `updated_tokens` (
  `token_id` int(10) unsigned NOT NULL,
  `dict_revision` int(10) unsigned NOT NULL,
  KEY `token_id` (`token_id`),
  KEY `dict_revision` (`dict_revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_aliases`
--

DROP TABLE IF EXISTS `user_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_aliases` (
  `primary_uid` smallint(5) unsigned NOT NULL,
  `alias_uid` smallint(5) unsigned NOT NULL,
  UNIQUE KEY `alias_uid` (`alias_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `user_id` smallint(5) unsigned NOT NULL,
  `badge_id` tinyint(3) unsigned NOT NULL,
  `shown` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `shown` (`shown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_badges_types`
--

DROP TABLE IF EXISTS `user_badges_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges_types` (
  `badge_id` tinyint(3) unsigned NOT NULL,
  `badge_name` varchar(127) NOT NULL,
  `badge_descr` text NOT NULL,
  `badge_image` varchar(255) NOT NULL,
  `badge_group` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`badge_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_options`
--

DROP TABLE IF EXISTS `user_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_options` (
  `option_id` smallint(5) unsigned NOT NULL,
  `option_name` varchar(128) NOT NULL,
  `option_values` varchar(64) NOT NULL,
  `default_value` smallint(6) NOT NULL,
  `order_by` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_options_values`
--

DROP TABLE IF EXISTS `user_options_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_options_values` (
  `user_id` smallint(5) unsigned NOT NULL,
  `option_id` smallint(6) NOT NULL,
  `option_value` smallint(6) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_permissions` (
  `user_id` smallint(5) unsigned NOT NULL,
  `perm_admin` tinyint(3) unsigned NOT NULL,
  `perm_adder` tinyint(3) unsigned NOT NULL,
  `perm_dict` tinyint(3) unsigned NOT NULL,
  `perm_disamb` tinyint(3) unsigned NOT NULL,
  `perm_check_tokens` tinyint(3) unsigned NOT NULL,
  `perm_check_morph` tinyint(3) unsigned NOT NULL,
  `perm_merge` tinyint(3) unsigned NOT NULL,
  `perm_syntax` tinyint(3) unsigned NOT NULL,
  `perm_check_syntax` tinyint(3) unsigned NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_rating_log`
--

DROP TABLE IF EXISTS `user_rating_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_rating_log` (
  `user_id` smallint(5) unsigned NOT NULL,
  `delta` smallint(6) NOT NULL,
  `pool_id` smallint(5) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_stats`
--

DROP TABLE IF EXISTS `user_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_stats` (
  `user_id` smallint(5) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `param_id` smallint(5) unsigned NOT NULL,
  `param_value` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `param_id` (`param_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_teams`
--

DROP TABLE IF EXISTS `user_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_teams` (
  `team_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `team_name` varchar(128) NOT NULL,
  `creator_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`team_id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tokens` (
  `user_id` smallint(5) unsigned NOT NULL,
  `token` int(10) unsigned NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(120) NOT NULL,
  `user_passwd` varchar(32) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_reg` int(10) unsigned NOT NULL,
  `user_shown_name` varchar(120) NOT NULL,
  `user_team` smallint(5) unsigned NOT NULL,
  `user_level` tinyint(3) unsigned NOT NULL,
  `user_shown_level` tinyint(3) unsigned NOT NULL,
  `user_rating10` int(10) unsigned NOT NULL,
  `show_game` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `user_team` (`user_team`),
  KEY `user_rating10` (`user_rating10`)
) ENGINE=InnoDB AUTO_INCREMENT=3455 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
