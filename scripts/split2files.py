import os.path
import argparse

try:
    import xml.etree.cElementTree as et
except ImportError:
    import xml.etree.ElementTree as et


class OpcorpSplitter():
    def __init__(self):
        self._process_cli_args()

    def _process_cli_args(self):
        parser = argparse.ArgumentParser(description='Split opencorpora single file into text files')
        parser.add_argument('in_file', metavar='CORPUS_FILE', help='path to opencorpora xml file')
        parser.add_argument('out_path', metavar='OUTPUT_DIR', help='path to extract files to')
        parser.add_argument('-v', '--verbose', help='show more/less output; default = 1', type=int, choices=[0, 1, 2], default=1)
        parser.parse_args(namespace=self)

    def process(self):
        print('Started processing')


if __name__ == "__main__":
    splitter = OpcorpSplitter()
    splitter.process()
