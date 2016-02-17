import os
import argparse
import xml.sax


from opcorp_basic_parsers import DictionaryEndParseException
from opcorp_parsers import OpcorpDictionaryGrammemeHandler

encoding = 'utf-8'


"""
exports a list of grammemes from the dictionary file
"""
def export_grammeme_list(in_file, output):
    try:
        parser = xml.sax.parse(in_file, OpcorpDictionaryGrammemeHandler(output, encoding))
    except DictionaryEndParseException:
        pass

def _process_args():
    parser = argparse.ArgumentParser(description="Export the grammemes from the dictionary into a tab-delimited file")
    
    parser.add_argument('dictionary_dump',
                            help='path to the dictionary xml file')
    
    parser.add_argument('output_file',
                            help='path to the resulting tsv file')
   
    return parser.parse_args()

def _ask_for_overwrite(filename):
    answer = None
    while answer not in ['', 'y', 'n']:
        answer = input('Output file {0} already exists. Overwrite it? '
                           '{{[n],y}}'.format(filename))

    return not answer.lower() in ['', 'n']


def _check_args(args):
    if not os.path.exists(args.dictionary_dump):
        raise Exception('corpus dictionary_dump does not exist:%s' % args.dictionary_dump)
    
    if os.path.exists(args.output_file):
        return _ask_for_overwrite(args.output_file)
    
    
    return True

def main():
    args = _process_args()
    if not _check_args(args):
        return
    export_grammeme_list(args.dictionary_dump, args.output_file)
    print('exported to %s' % args.output_file)
    
    
if __name__ == "__main__":
    main()

