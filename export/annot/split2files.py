import os
import sys
import json
import datetime
import argparse
import shutil
import xml.sax
from xml.sax.saxutils import escape

try:
    import xml.etree.cElementTree as ET
except ImportError:
    import xml.etree.ElementTree as ET


class OpcorpContentHandler(xml.sax.ContentHandler):
    def __init__(self, out_path, encoding):
        super().__init__()
        self.file = None
        self.out_path = out_path
        self.encoding = encoding
    def _new_file(self, fid):
        path = os.path.join(self.out_path, '{}.xml'.format(fid))
        self.file = open(path, 'wb')
        bang_u = '<?xml version="1.0" encoding="{}"?>'.format(self.encoding)
        self.file.write(bang_u.encode(self.encoding))

    def _close_file(self):
        self.file.close()

    def _gen_start_tag(self, name, attrs):
        if not attrs:
            st_u = '<{}>',format(name)
        else:
            attributes = ' '.join('{}="{}"'.format(k, escape(v)) \
            for k, v in attrs.items())
            st_u = '<{} {}>'.format(name, attributes)

        return st_u.encode(self.encoding)

    def _gen_end_tag(self, name):
        st_u = '</{}>'.format(name)
        return st_u.encode(self.encoding)

    def startElement(self, name, attrs):
        if name == 'text':
            fid = attrs.get('id')
            self._new_file(fid)
            self.file.write(self._gen_start_tag(name, attrs))
        elif name != 'annotation':  # all tags that are in text
            self.file.write(self._gen_start_tag(name, attrs))
        else:  # annotation
            with open(os.path.join(self.out_path, 'annotation.json'), 'w') as annot:
                json.dump({k: v for k, v in attrs.items()}, annot)


    def endElement(self, name):
        if name != 'annotation':
            self.file.write(self._gen_end_tag(name))
        if name == 'text':
            self._close_file()

    def characters(self, content):
        if content.strip():
            self.file.write(content.strip().encode(self.encoding))


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
        parser.add_argument('-p', '--parser', default='sax', choices=['dom', 'sax'],
                            help='parser to use; default=sax')
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
                shutil.rmtree(self.output)
                os.makedirs(self.output)
        else:
            os.makedirs(self.output)

        try:
            if self.parser == 'sax':
                parser = xml.sax.parse(self.in_file, OpcorpContentHandler(self.output, self.encoding))

            elif self.parser == 'dom':
                for ev, el in ET.iterparse(self.in_file):
                    if ev == 'end' and el.tag == 'text':
                        out_file_path = os.path.join(self.output,
                                                     '{0}{1}'.format(el.get('id'),
                                                                   '.xml'))
                        if self.verbosity == 2:
                            print('file {0} [id={1}] will be written to '
                                  '{2}'.format(el.get('name'), el.get('id'),
                                               out_file_path))

                        if os.path.exists(out_file_path) and self.verbosity > 0:
                            print('file {0} already exists. Maybe duplicate '
                                  'ids or not empty output '
                                  'location?'.format(out_file_path))

                        tt = ET.ElementTree(element=el)
                        tt.write(out_file_path, encoding=self.encoding,
                                 xml_declaration=True)
                        tt = None
                        el.clear()

                    elif ev == 'end' and el.tag == 'annotation':
                        annotation_path = os.path.join(self.output,
                                                       'annotation.json')
                        if self.verbosity == 2:
                            print('annotation file will be written to '
                                  '{0}'.format(annotation_path))

                        if os.path.exists(annotation_path) and self.verbosity > 0:
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
