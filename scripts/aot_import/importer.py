#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys
from os.path import abspath, dirname, join
root = join(dirname(abspath(sys.argv[0])), "../..")
sys.path.append(join(root, 'python'))
from Annotation import AnnotationEditor, Lexeme

CONFIG_PATH = join(root, "config.ini")


def do_import(editor, stream):
    in_lemma = False
    new_lemma = None
    skip = False
    for line in stream:
        line = line.strip().decode('utf-8')
        if line.isdigit() or not line:
            in_lemma = False
        else:
            form_text, gram = line.split('\t')
            form_text = form_text.lower().encode('utf-8')
            assert all(c in 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя-' for c in form_text)
            gram_parts = gram.encode('utf-8').split(' ')
            gram_lemma = gram_parts[0].split(',')
            gram_form = gram_parts[1].split(',') if len(gram_parts) == 2 else []
            if not in_lemma:
                if new_lemma is not None:
                    new_lemma.save()
                    new_lemma = None
                found = editor.find_lexeme_by_lemma(form_text, ['ADVB'])
                if len(found) > 0:
                    print("found {} {}: {}".format(len(found), form_text, ','.join(found[0].lemma['gram'])))
                    skip = True
                else:
                    new_lemma = Lexeme(lemma=form_text, editor=editor)
                    new_lemma.add_lemma_gram(gram_lemma)
                    new_lemma.add_form(form_text, gram_form)
                    skip = False
                in_lemma = True
            elif not skip:
                new_lemma.add_form(form_text, gram_form)

    if new_lemma is not None:
        new_lemma.save()


def main():
    editor = AnnotationEditor(CONFIG_PATH)
    do_import(editor, sys.stdin)
    #editor.commit()


if __name__ == "__main__":
    main()
