#include <list>
#include <string>

#include "sentence.h"

#ifndef __CORPORA_IO_H
#define __CORPORA_IO_H

typedef std::list<Sentence> SentenceCollection;

void readCorpus(const std::string &fn, SentenceCollection &sc);

#endif
