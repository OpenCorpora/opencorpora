
#include <vector>
#include <algorithm>
#include <iterator>
#include <sstream>

#include "corpus_stat.h"


using namespace std;

void CorpusStat::generate() {

  for (size_t sid = 0; sid < sc.size(); sid++) {
    const Sentence& rsent = sc[sid];
    vector<TagSet> vPOST;
    vPOST.resize(rsent.size());

    for (size_t j = 0; j < rsent.size(); j++)
      vPOST[j] = rsent.getToken(j).getPOST();

    for (size_t i = 1; i < rsent.size()-1; i++) {
      TagSet POST = vPOST[i];

      std::map<TagSet, std::vector<std::set<Condition> > >::iterator it = mapTagSet2Features.find(POST);
      if (mapTagSet2Features.end() == it)
        mapTagSet2Features[POST].resize(((rightTagContext > rightWordContext) ? rightTagContext : rightWordContext) + ((leftTagContext > leftWordContext) ? leftTagContext : leftWordContext) + 1);
      it = mapTagSet2Features.find(POST);


      // Search index
      CorpusPos Here(sid, i);
//cerr << "i == " << i << "\t";      
      for (size_t p = (i >= leftTagContext ? i-leftTagContext : 0);
           p < (i+rightTagContext <= rsent.size() ? i+rightTagContext : rsent.size());
           p++) {
        //if (p == i) continue; // Don't check this word
        //cerr << vPOST[p].str();
        Condition c(p - i, vPOST[p]);
        entries[c].insert(Here);
//cerr << "p == " << p << endl;
        if (p != i && POST.size() > 1)
          mapTagSet2Features[POST][p - i + leftTagContext].insert(c);
      }

      for (size_t p = (i >= leftWordContext ? i-leftWordContext : 0);
           p < (i+rightWordContext <= rsent.size() ? i+rightWordContext : rsent.size());
           p++) {

        if (mapFormFreq[rsent.getToken(p).getText()] > 10) {
          // Yes, we accept current word as a feature.
          Condition c(p - i, rsent.getToken(p).getText());
          entries[c].insert(Here);

          if (POST.size() > 1)
            mapTagSet2Features[POST][p - i + leftWordContext].insert(c);
        }
      }

      // Simple TagSet frequencies
      mapTagSetFreq[POST] += 1;
 
    }
  }
}

void CorpusStat::calcFormFreq() {
  for (size_t sid = 0; sid < sc.size(); sid++) {
    const Sentence& rsent = sc[sid];

    for (size_t j = 1; j < rsent.size()-1; j++)
      mapFormFreq[rsent.getToken(j).getText()] += 1;
  }
}

size_t CorpusStat::getFreq(const TagSet &ts, const Context &c) const {
  set<CorpusPos> s;

  Condition focusWord(0, ts);
  map<Condition, set<CorpusPos> >::const_iterator fw_idx = entries.find(focusWord);
  if (entries.end() != fw_idx)
    s = fw_idx->second;

  if (0 == s.size())
    return 0;

  set<Condition>::const_iterator cit = c.begin();
  while (c.end() != cit) {
    map<Condition, set<CorpusPos> >::const_iterator idx = entries.find(*cit);
    if (entries.end() != idx) {
      if (0 == s.size())
        s = idx->second;
      else {
        set<CorpusPos> _s;
        set_intersection(s.begin(), s.end(), idx->second.begin(), idx->second.end(), inserter(_s,_s.begin()));
        if (0 == _s.size())
          return 0;
        s = _s;
      }
    }
    cit++;
  }

  return s.size();
}

string CorpusStat::toString() const {
  stringstream ss;
  map<Condition, std::set<CorpusPos> >::const_iterator cit = entries.begin();
  while (entries.end() != cit) {
    ss << "Condition: " << cit->first.str() << endl;
    ss << "Positions: ";
    for (set<CorpusPos>::const_iterator c = cit->second.begin(); cit->second.end() != c; c++) {
      ss << c->str() << " ";
    }
    ss << endl << endl;
    cit++;
  }

  ss << endl << "Tagset frequencies" << endl;
  map<TagSet, size_t>::const_iterator citf = mapTagSetFreq.begin();
  while (mapTagSetFreq.end() != citf) {
    ss << citf->first.str() << " " << citf->second << endl;
    citf++;
  }

  ss << endl << "Tagset2features table" << endl;
  map<TagSet, vector<set<Condition> > >::const_iterator citv = mapTagSet2Features.begin();
  while (mapTagSet2Features.end() != citv) {
    for (size_t i = 0; i < citv->second.size(); i++) {
      ss << citv->first.str() << " " << i << " ";
      set<Condition>::const_iterator cit = citv->second[i].begin();
      while (citv->second[i].end() != cit) {
        ss << cit->str() << " ";
        cit++;
      }
      ss << endl;
    }
    citv++;
  }

  return ss.str();
}
