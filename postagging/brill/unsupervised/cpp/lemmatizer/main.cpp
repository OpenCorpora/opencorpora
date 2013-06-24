#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <list>
#include <set>
#include <map>
#include <string>

#include "tag.h"
#include "token.h"
#include "sentence.h"
#include "corpora_io.h"
#include "dict.h"

using namespace std;

SentenceCollection corpus;
Dict dict;

int main(int argc, char **argv) {

  dict.load("~/dict.opcorpora.txt");
  cout << "end" << endl; //cin >> ws; return -1;
  if (argc > 1)
    readCorpus(argv[1], corpus);
  else {
    cerr << "corpus file is missing" << endl;
    return -1;
  }

  cerr << "size == " << corpus.size() << endl;

  SentenceCollection::const_iterator cit = corpus.begin();
  while (corpus.end() != cit) {
    stringstream ss;
    ss << "sent" << endl;

    for (size_t i = 0; i < cit->size(); i++) {
      int id;
      const Token &t = cit->getToken(i, id);

      if (t.getPOST().hasTag(T(SBEG)) || t.getPOST().hasTag(T(SEND))) {
        continue;
      } 

      ss << id << '\t' << t.getText() << '\t';

      if (t.isWord()) {
        const set<MorphInterp>& h = dict.lookup(t.getText().c_str());
        ss << toString(h) << endl; 
      } else {
        const set<MorphInterp> h = t.getMorph();
        ss << toString(h) << endl;
      }
      
      //cout << "//" << i << '\t' << t.str() << endl;
      //cout << i << '\t' << t.getText() << '\t' << t.getPOST().str() << endl; 
    }

    ss << "/sent" << endl;
    cout << ss.str();
    cit++;
  }

  return 0;
}

