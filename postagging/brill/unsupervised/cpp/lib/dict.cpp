#include <fstream>
#include <iostream>

#include "dict.h"

using namespace std;
using namespace std::tr1;

const std::set<MorphInterp>& Dict::lookup(const Glib::ustring &str) const {
  // to uppercase
  string searchKey = str.uppercase();
  //unordered_map<string, std::set<MorphInterp> >::const_iterator cit = d.find(searchKey);
  unordered_map<string, size_t>::const_iterator cit = d.find(searchKey);

  if (d.end() == cit)
    return unknown;

  //return cit->second;
  return v_interp[cit->second];
}

void Dict::load(const std::string &fn) {
  ifstream f(fn.c_str());
  if (!f.is_open()) {
    cerr << "can't open file \"" << fn << "\"" << endl;
    throw;
  }

//  unordered_map<set<MorphInterp>, size_t> t;

  size_t c = 0;
  string t;
  unsigned int lemmaId = 0;
  while (getline(f, t)) {
    if (0 == t.size()) {
      // save lemma?
    } else {
      stringstream ss(t);
      if (t[0] <= '9' && t[0] >= '0') {
        ss >> lemmaId;
        cerr << lemmaId << "\r";
      } else {
        string form, grm;
        ss >> form;
        string g;
        while (ss >> g) {
          vector<string> v;
          split(g, ',', v);
          for (size_t i = 0; i < v.size(); ++i) {
            if (grm.size() > 0)
              grm += " ";
            grm += v[i];
          }
        }
        MorphInterp mi(lemmaId, grm);
        unordered_map<string, size_t>::const_iterator cit = d.find(form);
        if (d.end() == cit) {
          set<MorphInterp> smi;
          smi.insert(mi);
          v_interp.push_back(smi);
          d[form] = v_interp.size() - 1;
        } else {
          v_interp[cit->second].insert(mi);
        }

        //d[form].insert(mi); 
        ++c;
      }
    }
    //cerr << c << " lemmata\r";
  }

  cerr << c << " forms" << endl;
  //cin >> ws;
}
