#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os
import sys
import datetime
import codecs
from Annotation import AnnotationEditor
import argparse



DELIMITER_LEMMA_GR = '\t'
DELIMITER_LEMMA_GR_SINGLE_LINE = ' '
DELIMITER_LEMMATA = '\t'
DELIMITER_GR_LIST = ','


"""
a file which has the following structure:
lemma1\tgrammmemes
lemma2\tgrammmemes
empty line
"""
TYPE_SEPARATE_LINES = 1

"""
a file which has the following structure:
lemma1 grammmemes\tlemma2
"""
TYPE_ONE_LINE = 2

"""
parses a file with links and inserts them into the database
"""
def add_links_from_file(filename, link_type, file_type, config_file, is_to_print_time, is_to_add_several_lexemes, is_dry_run, revset_id=None, comment=""):
    start = datetime.datetime.now()
        
    annotation_editor = AnnotationEditor(config_file)
    link_list = parse_links_from_file(filename, link_type, file_type)

    if comment == "":
        comment = os.path.basename(filename)
    add_links(annotation_editor, link_list, revset_id, comment, is_to_add_several_lexemes, is_dry_run)
    
    if is_to_print_time:
        print('time elapsed for add_links_from_file:{0}'.format(datetime.datetime.now() - start))

"""
parses 
"""
def parse_links_from_file(filename, link_type, file_type):
    if file_type == TYPE_SEPARATE_LINES:
        return parse_links_two_lines(filename, link_type)
    if file_type == TYPE_ONE_LINE:
        return parse_links_one_line(filename, link_type)
    raise Exception('unknown file type: %s' % file_type)

def parse_links_two_lines(filename, link_type):
    link_list = []
    lemma1 = None
    grammemes1 = None
    
    lemma2 = None
    grammemes2 = None
    
    has_been_added = False
    with codecs.open(filename, 'r', 'utf-8') as fin:
        for index, line in enumerate(fin):
            if index % 3 == 0: 
                lemma1, grammemes1 = get_lemma_grammemes(line)
            elif index % 3 == 1:
                has_been_added = False
                lemma2, grammemes2 = get_lemma_grammemes(line)
            else:
                has_been_added = True
                link_list.append(((lemma1, grammemes1), (lemma2, grammemes2), link_type))
    if not has_been_added:
        link_list.append(((lemma1, grammemes1), (lemma2, grammemes2), link_type))         
        
    return link_list
    
    
def parse_links_one_line(filename, link_type):
    link_list = []
    with codecs.open(filename, 'r', 'utf-8') as fin:
        for line in fin:
            lemma1, grammemes, lemma2 = get_lemmata_grammemes(line)
            link_list.append(((lemma1, grammemes), (lemma2, grammemes), link_type))
    return link_list

"""
finds the lexemes in the database for the lexemes from the file
and ands the links
"""
def add_links(annotation_editor, link_list, revset_id, comment, is_to_add_several_lexemes, is_dry_run):
    #first we check that we have all lexemes
    link_list_with_ids = find_lexemes_for_list(annotation_editor, link_list, is_to_add_several_lexemes)
    #then we add the links
    for (from_id, to_id, link_type) in link_list_with_ids:        
        annotation_editor.add_link(from_id, to_id, link_type, revset_id, comment, is_dry_run)
        
def find_lexemes_for_list(annotation_editor, link_list, is_to_add_several_lexemes):
    link_list_with_ids = []

    for (from_lemma_grammemes, to_lemma_grammemes, link_type) in link_list:
        try:
            from_ids = find_lexemes(annotation_editor, from_lemma_grammemes, is_to_add_several_lexemes)  
            to_ids = find_lexemes(annotation_editor, to_lemma_grammemes, is_to_add_several_lexemes)
        except LexemeException as e:
            sys.stderr.write("Exception: {}\n".format(e))
            continue

        for from_id in from_ids:
            for to_id in to_ids:
                link_list_with_ids.append((from_id, to_id, link_type))
    return list(set(link_list_with_ids))
        
        
def find_lexemes(annotation_editor, lemma_grammemes, is_to_add_several_lexemes = False):
    lemma = lemma_grammemes[0].encode('utf-8')
    grammemes = lemma_grammemes[1]
    
    lexemes = annotation_editor.find_lexeme_by_lemma(lemma, grammemes)
    if not lexemes:
        raise LexemeException('no lexemes with lemma=%s, grammemes=%s found:' % (lemma, grammemes)) 
    if not is_to_add_several_lexemes and len(lexemes) != 1:
        raise LexemeException('several lexemes with lemma=%s, grammemes=%s found:' % (lemma, grammemes))
    return list(set([lexeme._id for lexeme in lexemes]))
            

def get_lemma_grammemes(line_from_file):
    line_parts = line_from_file.strip().split(DELIMITER_LEMMA_GR)
    return line_parts[0], tuple([grammeme.encode('utf-8') for grammeme in line_parts[1].split(DELIMITER_GR_LIST)])

def get_lemmata_grammemes(line_from_file):
    line_parts = line_from_file.strip().split(DELIMITER_LEMMATA)
    lemma1_parts = line_parts[0].split(DELIMITER_LEMMA_GR_SINGLE_LINE)
    lemma1 = lemma1_parts[0]
    lemma2 = line_parts[1]
    
    grammemes = tuple([grammeme.encode('utf-8') for grammeme in lemma1_parts[1].split(DELIMITER_GR_LIST)])
    return lemma1, grammemes, lemma2

def process_args():
    parser = argparse.ArgumentParser(description="Add links for the lexemes from the file")
    
    
    parser.add_argument('config_filename',
                            help='path to the config.ini file for the database connection')
    
    parser.add_argument('link_filename',
                            help='path to the file with the links')

    parser.add_argument('link_type',
                            help='the type of the link')
    
    parser.add_argument('file_type',
                            help='the type of the file: 1=different lines; 2=single line', choices=[1, 2], type=int)
    
    parser.add_argument('-r', '--revset_id',
                            help='the revision id', type=int, default=None)
    
    parser.add_argument('-c', '--comment',
                            help='the comment for the action', default="")
 
    parser.add_argument('-t', '--time', action='store_true', default=False,
                            help='print execution time in the end')
    
    parser.add_argument('-s', '--is_to_add_several_lexemes',
                            help='True if several lexemes with identical properties can exist', action='store_true', default=False)
    
    parser.add_argument('-d', '--is_dry_run',
                            help='True if you do not want to make any changes to the database', action='store_true', default=False)
 
    
    return parser.parse_args()

def check_args(args):
    if not os.path.exists(args.link_filename):
        raise Exception('the file does not exist:%s' % args.link_filename)
    
    if not os.path.exists(args.config_filename):
        raise Exception('the file does not exist:%s' % args.config_filename)
    return True

class LexemeException(Exception):
    pass

def main():
    args = process_args()
    if not check_args(args):
        return

    
    add_links_from_file(args.link_filename, args.link_type, args.file_type, args.config_filename,
                       args.time, 
                        args.is_to_add_several_lexemes,
                        args.is_dry_run,
                        args.revset_id, args.comment)
    
    
if __name__ == "__main__":
    main()


    
