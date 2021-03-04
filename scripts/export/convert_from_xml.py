#!/usr/bin/python
# coding=utf-8
"""
:mod: This module intended to convert xml to different formats.
Input: filename for input, filename for output, mode ("plain" text, or "json" for json)
Output: plain text or json file.
"""
from __future__ import print_function
from logging import info
from collections import namedtuple
from json import dump
from sys import argv

__all__ = ["parse_opencorpora_xml"]

try:
    from lxml.etree import iterparse as iterator

    def xml_free(elem):
        elem.clear()
        while elem.getprevious() is not None:
            del elem.getparent()[0]


except ImportError:
    try:
        from xml.etree.cElementTree import iterparse as iterator
    except ImportError:
        from xml.etree.ElementTree import iterparse as iterator

    def xml_free(elem):
        elem.clear()


Link = namedtuple("Link", "from_ to_ type_")
Link.to_str = lambda x: "\t".join(x._asdict().values())
Lemma = namedtuple("Lemma", "lemm variants")
Lemma.to_str = lambda x: "\t".join(
    [
        " ".join(list(x.lemm.items())[0]),
        ",".join([" ".join(list(form.items())[0]) for form in x.variants]),
    ]
)
Grammeme = namedtuple("Grammeme", "parent alias description")
Grammeme.to_str = lambda x: "\t".join(x._asdict().values())


class OpenCorporaDictionary(object):
    def __init__(self, lexeme_dct, links_dct, grameme_dct, version):
        self.version = version
        self.links_dct = links_dct
        self.lexeme_dct = lexeme_dct
        self.grameme_dct = grameme_dct

    def generator_dictionary(self):
        return {
            "dictionary": {
                "version": self.version[0],
                "revision": self.version[1],
                "lemmata": {
                    idx: lemma._asdict() for idx, lemma in self.lexeme_dct.items()
                },
                "grammemes": {
                    key: grameme._asdict() for key, grameme in self.grameme_dct.items()
                },
                "links": {idx: link._asdict() for idx, link in self.links_dct.items()},
            }
        }

    def generator_tsv(self):
        yield "\t".join(["id", "root", "data", "extra"])
        yield "\t".join(["#header", "dictionary", "version", "revision"])
        yield "\t".join(["OpenCorpora", "dictionary", *self.version])
        yield "\t".join(["#lemmas", "lemma", "variants", "empty"])
        for idx, lemma in self.lexeme_dct.items():
            yield "\t".join((idx, lemma.to_str(), ""))
        yield "\t".join(["#gramemes", "parent", "alias", "description"])
        for key, grameme in self.grameme_dct.items():
            yield "\t".join((key, grameme.to_str()))
        yield "\t".join(["#links", "from", "to", "type"])
        for idx, link in self.links_dct.items():
            yield "\t".join((idx, link.to_str()))

    def dump_to_json(self, filename):
        with open(filename, "w") as f:
            dump(self.generator_dictionary(), f)

    def dump_to_tsv(self, filename):
        with open(filename, "w") as f:
            for s in self.generator_tsv():
                f.write(s + "\n")


def parse_opencorpora_xml(filename):
    """XML format parser"""

    def _compose_gramemes(elem):
        return ";".join([g.get("v") for g in elem.iter("g")])

    links_dct = dict()
    lexeme_dct = dict()
    grameme_dct = dict()

    tag_filter = set(("grammeme", "lemma", "link", "dictionary"))

    def _parse(filename, *, callback):
        for _, elem in iterator(filename):
            if elem.tag not in tag_filter:
                continue
            yield elem
            callback(elem)

    info("parsing XML dictionary")

    for elem in _parse(filename, callback=xml_free):
        if elem.tag == "lemma":  # the most often element
            lex_id = elem.get("id")
            lemma = elem.find("l")
            canonical_form = {lemma.get("t"): _compose_gramemes(lemma)}
            lexeme_dct[lex_id] = Lemma(
                canonical_form,
                [
                    {form.get("t").lower(): _compose_gramemes(form)}
                    for form in elem.iter("f")
                ],
            )
        elif elem.tag == "link":  # less often element
            links_dct[elem.get("id")] = Link(
                elem.get("from"),
                elem.get("to"),
                elem.get("type"),
            )
        elif elem.tag == "grammeme":  # the least often element
            grameme_dct[elem.find("name").text] = Grammeme(
                elem.get("parent"),
                elem.find("alias").text,
                elem.find("description").text,
            )

        elif elem.tag == "dictionary":  # only one element
            version = (elem.get("version"), elem.get("revision"))
            info("Dictionary version is {}".format(version))

    return OpenCorporaDictionary(lexeme_dct, links_dct, grameme_dct, version)


if __name__ == "__main__":
    ocd = parse_opencorpora_xml(argv[1])
    if argv[3] == "tsv":
        ocd.dump_to_tsv(argv[2])
    elif argv[3] == "json":
        ocd.dump_to_json(argv[2])
