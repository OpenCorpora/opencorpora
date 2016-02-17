import os
import xml.etree.ElementTree as ET
import unittest


"""
tests for the disambiguity resolver
"""
class DisambiguityTest(unittest.TestCase):
    CANON_POSTFIX = '.canon_out.xml'
    RESULT_POSTFIX = '.out.xml'
    DISAMBIGUATION_FOLDER = 'disamb_nonmod_tests'
    folder = os.path.join(os.path.dirname(os.path.abspath(__file__)), DISAMBIGUATION_FOLDER)

    def test_export(self):
        for filename in os.listdir(self.folder):
            if self._is_canonical_xml(filename):
                filename = os.path.join(self.folder, filename)
                resulting_filename = os.path.join(self.folder, self._get_resulting_filename(filename))
                self.assertTrue(self._compare_xml_files(filename, resulting_filename))

    def _compare_xml_files(self, filename1, filename2):
        print('comparing %s\nand\n%s' % (filename1, filename2))
        x1 = ET.parse(filename1).getroot()
        x2 = ET.parse(filename2).getroot()
        return self._xml_compare(x1, x2, print, False)


    """copied from https://bitbucket.org/ianb/formencode
    recursively compares two XML tags ignoring the order of attributes
    """
    def _xml_compare(self, x1, x2, reporter = None, stop_after_first_failure = True):
        result = True
        if x1.tag != x2.tag:
            if reporter:
                reporter('Tags do not match: %s and %s' % (x1.tag, x2.tag))

            result = False
            if stop_after_first_failure:
                return result
        for name, value in x1.attrib.items():
            if x2.attrib.get(name) != value:
                if reporter:
                    reporter('Attributes do not match: %s=%r, %s=%r'
                             % (name, value, name, x2.attrib.get(name)))
                result = False
                if stop_after_first_failure:
                    return result
        for name in x2.attrib.keys():
            if name not in x1.attrib:
                if reporter:
                    reporter('x2 has an attribute x1 is missing: %s'
                             % name)
                result = False
                if stop_after_first_failure:
                    return result
        if not self._text_compare(x1.text, x2.text):
            if reporter:
                reporter('text: %r != %r' % (x1.text, x2.text))
            return False
        if not self._text_compare(x1.tail, x2.tail):
            if reporter:
                reporter('tail: %r != %r' % (x1.tail, x2.tail))
            result = False
            if stop_after_first_failure:
                    return result
        cl1 = x1.getchildren()
        cl2 = x2.getchildren()
        if len(cl1) != len(cl2):
            if reporter:
                reporter('children length differs, %i != %i'
                         % (len(cl1), len(cl2)))
            result = False
            if stop_after_first_failure:
                return result
        i = 0
        for c1, c2 in zip(cl1, cl2):
            i += 1
            if not self._xml_compare(c1, c2, reporter=reporter):
                if reporter:
                    reporter('child %i does not match: %s'
                             % (i, c1.tag))
                result = False
                if stop_after_first_failure:
                    return result
        return result

    def _text_compare(self, t1, t2):
        return (t1 or '').strip() == (t2 or '').strip()

    def _is_canonical_xml(self, filename):
        return filename.endswith(self.CANON_POSTFIX)

    def _get_resulting_filename(self, filename):
        return filename.replace(self.CANON_POSTFIX, self.RESULT_POSTFIX)


if __name__ == '__main__':
    unittest.main()
