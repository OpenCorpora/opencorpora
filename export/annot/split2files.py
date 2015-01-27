#https://github.com/sepulchered/opcorp_splitter
import os
import sys
import json
import datetime
import argparse

try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET


class OpcorpSplitter():
    def __init__(self):
        self._process_cli_args()

    def _process_cli_args(self):
        parser = argparse.ArgumentParser(description='Split opencorpora single '
                                                     'file into text files')
        parser.add_argument('in_file', metavar='CORPUS_FILE',
                            help='path to opencorpora xml file')
        parser.add_argument('output', metavar='OUTPUT_PATH',
                            help='path to extract files to')
        parser.add_argument('-v', '--verbosity',
                            help='show more/less output; default = 1',
                            type=int, choices=[0, 1, 2], default=1)
        parser.add_argument('-e', '--encoding', default='utf-8',
                            help='encoding of output files; defaults to utf-8')
        parser.add_argument('-t', '--time', action='store_true', default=False,
                            help='print execution time in the end')
        parser.parse_args(namespace=self)

    def _ask_for_overwrite(self):  # input set for tests
        if not self.verbosity:  # silently overwrite if verbosity set to 0
            return True

        answer = None
        while answer not in ['', 'y', 'n']:
            answer = input('Output folder {0} already exists. Overwrite it? '
                           '{{[n],y}}'.format(self.output))

        if answer in ['', 'n']:
            return False
        else:
            return True

    def process(self):
        # check if input file exists
        if not os.path.exists(self.in_file):
            print('Invalid input file provided')
            sys.exit(1)

        # check if output path exists
        if os.path.exists(self.output):
            overwrite = self._ask_for_overwrite()

            if not overwrite:
                sys.exit(0)
        else:
            os.makedirs(self.output)

        try:
            for ev, el in ET.iterparse(self.in_file):
                if ev == 'end':
                    if el.tag == 'text':
                        out_file_path = os.path.join(self.output,
                                                     '{0}{1}'.format(el.get('id'),
                                                                   '.xml'))
                        if self.verbosity == 2:
                            print('file {0} [id={1}] will be written to '
                                  '{2}'.format(el.get('name'), el.get('id'),
                                               out_file_path))

                        if os.path.exists(out_file_path):
                            if self.verbosity > 0:
                                print('file {0} already exists. Maybe duplicate '
                                      'ids or not empty output '
                                      'location?'.format(out_file_path))

                        tt = ET.ElementTree(element=el)
                        tt.write(out_file_path, encoding=self.encoding,
                                 xml_declaration=True)
                        el.clear()

                    elif el.tag == 'annotation':
                        annotation_path = os.path.join(self.output,
                                                       'annotation.json')
                        if self.verbosity == 2:
                            print('annotation file will be written to '
                                  '{0}'.format(annotation_path))
                        if os.path.exists(annotation_path):
                            if self.verbosity > 0:
                                print('annotation file already exists in '
                                      'output location'.format(annotation_path))

                        with open(annotation_path, 'w') as annotation:
                            json.dump({'version': el.get('version'),
                                       'revision': el.get('revision')},
                                      annotation)
                        el.clear()

        except Exception as ex:
            print(ex)

if __name__ == "__main__":
    start = datetime.datetime.now()
    splitter = OpcorpSplitter()
    splitter.process()
    if splitter.time:
        end = abs(start-datetime.datetime.now())
        print('executed in {0} sec'.format(end.total_seconds()))
