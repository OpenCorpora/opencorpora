#!/usr/bin/env python2
# -*- coding: utf-8 -*-

import datetime
import xml.sax
import argparse
import os

from opcorp_sentence_parsers import OpcorpSentenceRemover

"""
creates a new corpus dump from the corpus file
removing sentences which contain ambiguous tokens
and paragraphs which contain no sentences
"""
def remove_ambiguous_sentences(corpus_filename, resulting_file_name, is_to_print_time):
    start = datetime.datetime.now()
    handler = OpcorpSentenceRemover(resulting_file_name, 'utf-8')
    xml.sax.parse(corpus_filename, handler)
    
    if is_to_print_time:
        print('time elapsed :{0}'.format(datetime.datetime.now() - start))

def _ask_for_overwrite(filename):
    answer = None
    while answer not in ['', 'y', 'n']:
        answer = input('Output file {0} already exists. Overwrite it? '
                           '{{[n],y}}'.format(filename))

    return not answer.lower() in ['', 'n']

def check_args(args):    
    if os.path.exists(args.resulting_corpus_dump):
        return _ask_for_overwrite(args.resulting_corpus_dump)

    return True


def process_args():
    parser = argparse.ArgumentParser(description="Exclude sentences which have ambiguous tokens")
    
    parser.add_argument('corpus_dump',
                            help='path to the opencorpora xml file')

    parser.add_argument('resulting_corpus_dump',
                            help='path to the resulting file')

 
    parser.add_argument('-t', '--time', action='store_true', default=False,
                            help='print execution time in the end')
    return parser.parse_args()

def main():
    args = process_args()
    if not check_args(args):
        return
    remove_ambiguous_sentences(args.corpus_dump, args.resulting_corpus_dump, args.time)
    
if __name__ == "__main__":
    main()