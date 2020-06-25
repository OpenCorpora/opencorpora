<?php
define('SEC_PER_DAY', 24 * 60  * 60);
define('MSEC_PER_DAY', SEC_PER_DAY * 1000);

define('PERM_ADMIN', 1);
define('PERM_DICT', 2);
define('PERM_ADDER', 3);
define('PERM_DISAMB', 4);
//define('PERM_CHECK_TOKENS', 5);
define('PERM_MORPH_MODER', 6);
define('PERM_MORPH_SUPERMODER', 7);
define('PERM_SYNTAX', 8);
define('PERM_SYNTAX_MODER', 9);
define('PERM_NE_MODER', 10);
define('PERM_MULTITOKENS', 11);

define('OPT_GRAMNAMES', 1);
define('OPT_ILANG', 2);
define('OPT_SAMPLES_PER_PAGE', 3);
define('OPT_MODER_SPLIT', 4);
define('OPT_NE_QUICK', 5);
define('OPT_NE_TAGSET', 6);
define('OPT_GAME_ON', 7);

define('DICT_UGC_PENDING', 0);
define('DICT_UGC_APPROVED', 1);
define('DICT_UGC_REJECTED', 2);

//define('STATS_TOKENIZER_SURE_RATIO', 7);
define('STATS_BROKEN_TOKEN_IDS', 28);
define('STATS_ANNOTATOR_DIVERGENCE_TOTAL', 34);

define('SENTENCE_QUALITY_NONE', 0);
define('SENTENCE_QUALITY_NO_AMBIG', 1);
define('SENTENCE_QUALITY_NO_AMBIG_OR_UNKN', 2);
define('SENTENCE_QUALITY_MAX', SENTENCE_QUALITY_NO_AMBIG_OR_UNKN);

define('MA_POOLS_STATUS_FOUND_CANDIDATES', 1);
define('MA_POOLS_STATUS_NOT_STARTED', 2);
define('MA_POOLS_STATUS_IN_PROGRESS', 3);
define('MA_POOLS_STATUS_ANSWERED', 4);
define('MA_POOLS_STATUS_MODERATION', 5);
define('MA_POOLS_STATUS_MODERATED', 6);
define('MA_POOLS_STATUS_TO_MERGE', 7);
define('MA_POOLS_STATUS_MERGING', 8);
define('MA_POOLS_STATUS_ARCHIVED', 9);

define('MA_SAMPLES_STATUS_OK', 0);
define('MA_SAMPLES_STATUS_ALMOST_OK', 1);
define('MA_SAMPLES_STATUS_NO_CORRECT_PARSE', 2);
define('MA_SAMPLES_STATUS_MISPRINT', 3);
define('MA_SAMPLES_STATUS_HOMONYMOUS', 4);
define('MA_SAMPLES_STATUS_MANUAL_EDIT', 5);  // for non-merged samples

define('MA_ANNOTATORS_PER_SAMPLE', 4);
define('MA_DEFAULT_POOL_SIZE', 50);
define('MA_ANSWER_OTHER', 99);
define('MA_TOTAL_TASKS_PLAN', 1333000);
define('MA_PAGE_SIZE_FOR_MODERATORS', 15);

define('NE_STATUS_NOT_STARTED', 0);
define('NE_STATUS_IN_PROGRESS', 1);
define('NE_STATUS_FINISHED', 2);

define('NE_ANNOT_TIMEOUT', 24 * 60 * 60);  // 24 hours

define('NE_OBJECT_DEFAULT_PROPS', serialize(
	array('ONLY_PERSON' => array('firstname', 'surname', 'patronymic', 'nickname'),
		  'NOT_PERSON'  => array('name', 'wikidata'),
		  'MIXED' => array('wikidata'))
));
