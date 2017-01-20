#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os
import datetime
import codecs
from Annotation import AnnotationEditor
import argparse

ENDING_COMPARATIVE = u'ее'
ENDING_ADVERB = u'о'
COMPARATIVE_GLOSS = u'%"COMP"%'
ADVERB_GLOSS = u'%ADVB%'

def get_adv_comp_pairs(output_filename, config_file, is_to_print_time):
    start = datetime.datetime.now()
    adj_pairs = find_adv_comp_pairs(config_file)
    print('found %d pairs' % len(adj_pairs))
    
    write_pairs_to_file(adj_pairs, output_filename)
    if is_to_print_time:
        print('time elapsed:{0}'.format(datetime.datetime.now() - start))
        

def find_adv_comp_pairs(config_file):
    adj_pairs = []
    
    annotation_editor = AnnotationEditor(config_file)
    res = annotation_editor.find_lexeme_by_lemma_regex_gr_regex(u'%' + ENDING_COMPARATIVE, COMPARATIVE_GLOSS)
    
    for lexeme in res:
        lexeme_text = lexeme.lemma['text'].decode('utf-8')
        adv_stem = get_adverb_stem(lexeme_text)
        
        adverbs_positive_forms =  annotation_editor.find_lexeme_by_lemma_gr_regex(adv_stem, ADVERB_GLOSS)
        
        
        for adverbs_positive_form in adverbs_positive_forms:
            adverb_positive_lexeme_id = adverbs_positive_form._id
            adverb_positive_lemma = adverbs_positive_form.lemma['text'].decode('utf-8')
            
            adj_pairs.append(({'id':lexeme._id, 'text':lexeme_text}, {'id':adverb_positive_lexeme_id, 'text':adverb_positive_lemma}))     
            
    return adj_pairs 

def write_pairs_to_file(adj_pairs, output_filename):
    with codecs.open(output_filename, 'w', 'utf-8') as fout:
        for adj_pair in adj_pairs:
            fout.write(str(adj_pair[0]['id']) + '\t' + str(adj_pair[1]['id']) + '\t' + adj_pair[0]['text'] + '\t' + adj_pair[1]['text'] + os.linesep)
            
def get_adverb_stem(comparative_form):
    return comparative_form[0:-2].encode('utf-8') + ENDING_ADVERB
    
def process_args():
    parser = argparse.ArgumentParser(description="Generate a file with adverb comparative ids")
    
    
    parser.add_argument('config_filename',
                            help='path to the config.ini file for the database connection')
    
    parser.add_argument('output_filename',
                            help='path to the output file')

    parser.add_argument('-t', '--time', action='store_true', default=False,
                            help='print execution time in the end')
    return parser.parse_args()

def check_args(args):
    if not os.path.exists(args.config_filename):
        raise Exception('the file does not exist:%s' % args.config_filename)
    return True
    
def main():
    args = process_args()
    if not check_args(args):
        return

    
    get_adv_comp_pairs(args.output_filename, args.config_filename, args.time)
    


if __name__ == "__main__":
    main()
