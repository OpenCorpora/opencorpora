#include <string>
#include <fstream>
#include <sstream>
#include <iostream>

#include "token.h"
#include "sentence.h"
#include "corpora_io.h"

using namespace std;

set<MorphInterp> makeVariants(const string &s) {
  set<MorphInterp> r;
  MorphInterp ts(0, s);
  r.insert(ts);
  return r;
}

void readCorpus(const string &fn, SentenceCollection &sc) {
  ifstream f(fn.c_str());
  if (!f.is_open()) {
    cerr << "can't open \"" << fn << endl;
    throw;
  }

  string s;
  Sentence sent;
  while (getline(f, s)) {
    //cerr << "reading line \"" << s << "\"" << endl;
    if ("sent" == s) {
      Token t("SentBegin", makeVariants("SBEG"));
      sent.push_back(t);
    } else if ("/sent" == s) {
      Token t("SentEnd", makeVariants("SEND"));
      sent.push_back(t);
      sc.push_back(sent);
      sent.clear();
    } else {
      vector<string> fields;
      split(s, '\t', fields);

      int id;
      string word;

      stringstream ss(s);
      ss >> id >> word;

      set<MorphInterp> variants;
      for (size_t i = 2; i < fields.size(); i++) {
        if (0 == fields[i].size())
          continue;

        stringstream ss(fields[i]);
        unsigned int lemmaId;
        string lemma;
        ss >> lemmaId >> lemma;
        
        string sgrm;
        string t;
        while (ss >> t) { 
          if (sgrm.size() > 0) sgrm += " ";
          sgrm += t; 
        }

        MorphInterp ts(lemmaId, sgrm); 
        if (0 == ts.size()) {
          cerr << "\"" << s << "\" - \"" << sgrm << "\"" << sgrm.size() << endl;
          throw;
        }
        variants.insert(ts);
      }

      Token t(word, variants);
      sent.push_back(t, id);
    }
  }

  if (sent.size() > 0) 
    sc.push_back(sent);
}
