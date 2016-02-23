#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import codecs
import os
import argparse
import xml.sax
import datetime
import zipfile
import io

from opcorp_parsers import OpcorpTokenVariantRemover
from opcorp_parsers import OpcorpTokenNormalizer
import no_homonymy_constants



STRATEGY_ALL = 'a'

POOL_FILENAME = 'pools.txt'
POOL_FILE_PREFIX = 'pool_'
POOL_FILE_EXT = '.tab'
POOL_FILE_DELIMITER = '\t'

POOL_FILE_DECISION_TOKEN_INDEX = 1
POOL_FILE_DECISION_DECISION_INDEX = 4

POOL_DESCRIPTION_POOL_INDEX = 0
POOL_DESCRIPTION_STATUS_INDEX = -1
POOL_DESCRIPTION_TYPE_INDEX = 1

POOL_MODERATED_STATUS = 9

"""https://github.com/OpenCorpora/opencorpora/issues/537
deletes the variants which the annotators have agreed on
"""
def generate_no_homonymy_dump(pool_folder, corpus_filename, resulting_file_name, strategy, is_to_print_time, grammeme_file):
    start = datetime.datetime.now()
    
    tokens_with_agreement = find_tokens_with_agreement(pool_folder, strategy)
    
    if is_to_print_time:
        print('time elapsed for tokens_with_agreement:{0}'.format(datetime.datetime.now() - start))
        
    if not tokens_with_agreement:
        print('no tokens in unmoderated pools which annotators agreed on')
        return  
    remove_homonymy_for_tokens(corpus_filename, resulting_file_name, tokens_with_agreement, is_to_print_time, grammeme_file)

"""finds the tokens which the annotators have agreed on"""
def find_tokens_with_agreement(pool_folder, strategy):
    return get_tokens_with_agreement_from_pools(get_unmoderated_pools(pool_folder), pool_folder, strategy)

"""generates a dump filtered from the variants which the annotators haven't chosen"""
def remove_homonymy_for_tokens(corpus_filename, resulting_filename, tokens_with_agreement, is_to_print_time, grammeme_file):
    start = datetime.datetime.now()
    removed_vars_filename, tokens_max_variant_arrays = copy_xml_removing_variants(corpus_filename, tokens_with_agreement)
    
    if is_to_print_time:
        print('time elapsed for remove_homonymy_for_tokens:{0}'.format(datetime.datetime.now() - start))
    
    print('non-normalized file exported to: %s' % removed_vars_filename)
    
    start = datetime.datetime.now()
    
    
    normalize_corpus_file(removed_vars_filename, resulting_filename, tokens_max_variant_arrays, grammeme_file)
    
    if is_to_print_time:
        print('time elapsed for normalize_corpus_file:{0}'.format(datetime.datetime.now() - start))
        
    print('normalized file exported to: %s' % resulting_filename)
   
"""removes the grammemes filtered by the annotators
saves the resulting xml into a new file
""" 
def copy_xml_removing_variants(corpus_filename, tokens_with_agreement):
    removed_vars_filename = corpus_filename + '_removed_temp.xml'
    
    handler = OpcorpTokenVariantRemover(removed_vars_filename, tokens_with_agreement, 'utf-8')
    xml.sax.parse(corpus_filename, handler)
    return removed_vars_filename, handler.tokens_max_variant_arrays
    
"""
deletes the variants which are subsets of other variants
"""
def normalize_corpus_file(removed_vars_filename, normalized_filename, tokens_max_variant_arrays, grammeme_file):
    handler = OpcorpTokenNormalizer(normalized_filename, tokens_max_variant_arrays, 'utf-8', grammeme_file)
    xml.sax.parse(removed_vars_filename, handler)


def get_tokens_with_agreement_from_pools(unmoderated_pools, pool_folder, strategy):
    tokens = {}
    for unmoderated_pool in unmoderated_pools:
        gather_tokens_from_pool(unmoderated_pool, pool_folder, tokens, strategy)
    filter_tokens_with_agreement(tokens, strategy) 
    return tokens

def gather_tokens_from_pool(unmoderated_pool, pool_folder, tokens,strategy):
    fname = POOL_FILE_PREFIX + unmoderated_pool[0] + POOL_FILE_EXT
    if pool_folder.endswith('.zip'):
        cmgr = zipfile.ZipFile(pool_folder).open(fname)
    else:
        pool_filename = os.path.join(pool_folder, fname)
        if not os.path.exists(pool_filename):
            raise Exception('No pool file found %s ' % pool_filename)
        cmgr = codecs.open(pool_filename, 'r', 'utf-8')

    with cmgr as fin:
        for line in io.TextIOWrapper(fin, 'utf-8'):
            token_desc_parts = line.strip().split(POOL_FILE_DELIMITER)
            token_id = get_token_id(token_desc_parts)
            
            
            
            token_decisions, moderators_comment = get_token_decisions(token_desc_parts)            
            if not is_suitable_by_strategy(token_decisions, moderators_comment, strategy):
                continue
            
            if token_id in tokens:
                if unmoderated_pool[1] not in tokens[token_id]:
                    tokens[token_id][unmoderated_pool[1]] = []
                tokens[token_id][unmoderated_pool[1]]['token_decisions'] += token_decisions
                tokens[token_id][unmoderated_pool[1]]['moderators_comment'] += moderators_comment
                    
            else:
                tokens[token_id] = {}
                tokens[token_id][unmoderated_pool[1]] = {}
                tokens[token_id][unmoderated_pool[1]]['token_decisions'] = token_decisions
                tokens[token_id][unmoderated_pool[1]]['moderators_comment'] = []
                
def filter_tokens_with_agreement(tokens, strategy):
    for token_id, token_pool_decisions in tokens.items():
        has_pool_types_deleted = True
        for pool_type, task_decisions in token_pool_decisions.items():
            decisions = task_decisions['token_decisions']
            moderators_comment = task_decisions['moderators_comment']
            strategy_decision = is_suitable_by_strategy(decisions, moderators_comment, strategy)
            if strategy_decision:
                has_pool_types_deleted = False
                tokens[token_id][pool_type] = strategy_decision
            else:
                tokens[token_id][pool_type] = None
                
        if has_pool_types_deleted:
            tokens[token_id] = None
                        


def is_suitable_by_strategy(token_decisions, moderators_comment, strategy):
    if strategy['strategy_type'] == STRATEGY_ALL:
        return check_decisions(token_decisions, moderators_comment, strategy)
    else:
        raise Exception('unrecognized strategy: %s ' % (strategy['strategy_type']))
     
def check_decisions(token_decisions, moderators_comment, strategy):   
    if moderators_comment and len(set(moderators_comment)) == 1:
        return moderators_comment[0]
    if strategy.get('min_number') is not None and (not token_decisions 
                                                   or len(token_decisions) < strategy.get('min_number')):
        return False
    if len(set(token_decisions)) > 1:
        return False
    if token_decisions[0] == no_homonymy_constants.DECISION_OTHER:
        return no_homonymy_constants.DECISION_UNKNOWN
    return token_decisions[0]



                
def get_unmoderated_pools(pool_folder):
    def get_pool_list(fin):
        pools = set()
        for line in fin:
            line_parts = line.strip().split(POOL_FILE_DELIMITER)
            pool_status = get_pool_status(line_parts)
            if is_unmoderated_pool(pool_status):
                pools.add((get_pool_id(line_parts), get_pool_type(line_parts)))
        return pools


    if pool_folder.endswith('.zip'):
        with zipfile.ZipFile(pool_folder).open(POOL_FILENAME) as fin:
            return get_pool_list(io.TextIOWrapper(fin, 'utf-8'))
    else:
        pool_description_path = os.path.join(pool_folder, POOL_FILENAME)
        if not os.path.exists(pool_description_path):
            raise Exception('No pool description file found %s ' % pool_description_path)
        with codecs.open(pool_description_path, 'r', 'utf-8') as fin:
            return get_pool_list(fin)

def is_unmoderated_pool(pool_status):
    return int(pool_status) < POOL_MODERATED_STATUS
    
def get_token_id(token_desc_parts):
    return token_desc_parts[POOL_FILE_DECISION_TOKEN_INDEX]


def get_token_decisions(token_desc_parts):
    decisions = token_desc_parts[POOL_FILE_DECISION_DECISION_INDEX:]
    moderators_comment = []
    if '' in decisions:
        moderators_comment_index = decisions.index('')
        moderators_comment = decisions[moderators_comment_index + 1:]
        decisions = decisions[:moderators_comment_index]
    return decisions, moderators_comment

def get_pool_id(pool_description_parts):
    return pool_description_parts[POOL_DESCRIPTION_POOL_INDEX]

def get_pool_status(pool_description_parts):
    return pool_description_parts[POOL_DESCRIPTION_STATUS_INDEX]

def get_pool_type(pool_description_parts):
    return pool_description_parts[POOL_DESCRIPTION_TYPE_INDEX]


def process_args():
    parser = argparse.ArgumentParser(description="Exclude variants of annotation from the dump based on the annotators'"
                                                    "common decisions")
    
    parser.add_argument('corpus_dump',
                            help='path to the opencorpora xml file')

    parser.add_argument('pool_folder',
                            help='path to the folder containing pool data')
    
    parser.add_argument('resulting_corpus_dump',
                            help='path to the resulting file')

    parser.add_argument('grammeme_list',
                            help='path to the grammeme list (tab-delimited, format: parent\tgrammeme_name\talias\tdescription)')
   
    parser.add_argument('-s', '--strategy',
                            help='strategy to use: a=all answers',
                            choices=['a'], default='a')
    
    parser.add_argument('-m', '--min_number',
                            help='minimum number of replies to use for strategy',
                            type=int, default=1)
 
    parser.add_argument('-t', '--time', action='store_true', default=False,
                            help='print execution time in the end')

    parser.add_argument('-y', dest='overwrite', action='store_true', help='overwrite destination file without prompting')

    return parser.parse_args()

def _ask_for_overwrite(filename):
    answer = None
    while answer not in ['', 'y', 'n']:
        answer = input('Output file {0} already exists. Overwrite it? '
                           '{{[n],y}}'.format(filename))

    return not answer.lower() in ['', 'n']


def check_args(args):
    if not os.path.exists(args.corpus_dump):
        raise Exception('corpus dump does not exist:%s' % args.corpus_dump)
    
    if not os.path.exists(args.pool_folder):
        raise Exception('pool folder does not exist:%s' % args.pool_folder)

    if not os.path.exists(args.grammeme_list):
        raise Exception('pool grammeme_list does not exist:%s' % args.grammeme_list)
    
    if os.path.exists(args.resulting_corpus_dump) and not args.overwrite:
        return _ask_for_overwrite(args.resulting_corpus_dump)
    
    
    return True

    
def main():
    args = process_args()
    if not check_args(args):
        return
    
    strategy = {'strategy_type':args.strategy, 'min_number':args.min_number}
    generate_no_homonymy_dump(args.pool_folder, args.corpus_dump, args.resulting_corpus_dump, strategy, args.time, args.grammeme_list)
    
    
if __name__ == "__main__":
    main()
