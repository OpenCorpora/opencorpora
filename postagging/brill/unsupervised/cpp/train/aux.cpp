#include <string>
#include <map>
#include <list>

#include "tag.h"
#include "sentence.h"
#include "corpora_io.h"

#include "brill.h"

using namespace std;

string PrintSC(const SentenceCollection &sc) {
  stringstream ss;

  SentenceCollection::const_iterator cit = sc.begin();
  while (sc.end() != cit) {
    ss << cit->str() << endl;
    cit++;
  }

  return ss.str();
}

string PrintRules(const list<Rule>& lr) {
  stringstream ss;

  list<Rule>::const_iterator cit = lr.begin();
  while (lr.end() != cit) {
    ss << cit->str() << endl;
    cit++;
  }

  return ss.str();
}

/*string toString(const map<TagSet, size_t> &m) {
  map<TagSet, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first.str() << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}*/

string toString(const map<string, size_t> &m) {
  map<string, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}
