#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <list>
#include <set>
#include <map>
#include <string>
#include <algorithm>

#include "tag.h"
#include "token.h"
#include "sentence.h"
#include "corpora_io.h"

using namespace std;

string toString(const map<TagSet, size_t> &m);
string toString(const map<string, size_t> &m);

struct TagStat {
  size_t freq;
  map<TagSet, size_t> leftTag;
  map<TagSet, size_t> rightTag;
  map<string, size_t> leftWord;
  map<string, size_t> rightWord;

  string str() const;
};

class Rule;

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat);
float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule>& knownRules); 
void ApplyRule(SentenceCollection &sc, const Rule &rule);

string PrintRules(const list<Rule>& lr);
string PrintSC(const SentenceCollection &sc);

map<TagSet, TagStat> tagStat;

int main(int argc, char **argv) {
  if (argc <= 1) {
    cerr << "corpus file is missing" << endl;
    return -1;
  }

  for (int i = 1; i < argc; i++) {
    SentenceCollection originalCorpus;
    SentenceCollection currentCorpus;

    tagStat.clear();

    cout << argv[i] << endl;
    readCorpus(argv[i], originalCorpus);

    currentCorpus = originalCorpus;
    list<Rule> rules;

    // TODO: делать это в цикле до тех пор, пока годных правил не останется
    float score = 0;
    do {
      score = DoOneStep(currentCorpus, tagStat, rules);
    } while (score > 0);

    //cerr << PrintRules(rules);

    ofstream f_orig((string(argv[i]) + ".orig").c_str());
    //cerr << "ORIGINAL:" << endl;
    f_orig << PrintSC(originalCorpus) << endl;

    ofstream f_final((string(argv[i]) + ".final").c_str());
    //cerr << "FINAL:" << endl;
    f_final << PrintSC(currentCorpus);

    ofstream f_rules((string(argv[i]) + ".rules").c_str());
    f_rules << PrintRules(rules);

    cout << rules.size() << " rules" << endl;
  }

  return 0;
}

string PrintSC(const SentenceCollection &sc) {
  stringstream ss;

  SentenceCollection::const_iterator cit = sc.begin();
  while (sc.end() != cit) {
    ss << cit->str() << endl;
    cit++;
  }

  return ss.str();
}

struct Condition {
  enum EType { tag, word };
  signed int pos;
  EType what;
  TagSet value;
  string form;

  Condition(signed int _pos, const TagSet& _value)
    : pos(_pos), what(tag), value(_value) { }

  Condition(signed int _pos, const string& _form)
    : pos(_pos), what(word), value(T(UNKN)), form(_form) { }

  inline bool match(const Sentence& s, size_t _pos) const {
    if (_pos + pos < 0 || _pos + pos > s.size() - 1)
      return false;

    if (tag == what && s.getToken(pos + _pos).getPOST() == value)
      return true;
    else if (word == what && s.getToken(pos + _pos).getText() == form)
      return true;

    return false;
  }

  string str() const {
    stringstream ss;
    if (tag == what)
      ss << pos << ":" << "tag" << "=" << value.str();
    else if (word == what)
      ss << pos << ":" << "word" << "=" << form;
    return ss.str();
  }
};

inline bool operator<(const Condition& a, const Condition& b) {
  return a.str() < b.str();
}

class Context {
  set<Condition> elements;

public:
  Context(signed int pos, const TagSet& value) {
    elements.insert(Condition(pos, value));
  }

  Context(signed int pos, const string& word) {
    elements.insert(Condition(pos, word));
  }

  inline bool match(const Sentence& s, size_t pos) const {
    set<Condition>::const_iterator cit = elements.begin();
    while (elements.end() != cit) {
      if (! cit->match(s, pos))
        return false;
      cit++;
    }
    return true;
  }

  string str() const {
    stringstream ss;
    set<Condition>::const_iterator cit = elements.begin();

    while (elements.end() != cit) {
      if (ss.str().size() > 0)
        ss << " & ";
      ss << cit->str();
      cit++;
    }

    return ss.str();
  }
};

struct Rule {
  TagSet from;
  Tag to;
  Context c;

public:
  Rule(const TagSet& _from, const Tag& _to, const Context& _c)
    : from(_from), to(_to), c(_c) { }

  string str() const {
    stringstream ss;
    ss << from.str() << " -> " << to.str() << " | " << c.str();
    return ss.str();
  }
};

inline bool operator<(const Rule& a, const Rule& b) {
  return a.str() < b.str();
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

template<class T>
struct less_by_second {
  map<T, float>& rmap;
  less_by_second(map<T, float>& _rmap) : rmap(_rmap) { }

  bool operator()(const T& a, const T& b) const {
    return rmap[a] > rmap[b];
  }
};

//void searchForRules(const TagSet& H, const map<TagSet, TagStat>& tStat, 

float constructRule(const map<Tag, size_t>& freq, const map<Tag, size_t>& incontext, const map<Tag, float>& inc2freq, Tag &bestY) {

  //Tag bestY;
  float bestScore = 0;

  map<Tag, size_t>::const_iterator pY = freq.begin();
  while (freq.end() != pY) {
    map<Tag, size_t>::const_iterator pZ = freq.begin();
    float maxValue = 0;
    Tag R;

    while (freq.end() != pZ) {
      if (pY->first == pZ->first) {
        pZ++;
        continue;
      }
      
      map<Tag, float>::const_iterator i2f_it = inc2freq.find(pZ->first);      
      if (i2f_it->second > maxValue) {
        maxValue = i2f_it->second;
        R = pZ->first;
      } 
 
       pZ++;
    }

    map<Tag, size_t>::const_iterator f_it = freq.find(pY->first);
    map<Tag, size_t>::const_iterator inc_it = incontext.find(pY->first);
    float score = inc_it->second - f_it->second * maxValue;

    if (score > bestScore) {
      bestScore = score;
      bestY = pY->first;
    }
          
    pY++;
  }

  return bestScore;
}

float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule> &knownRules) {
  tStat.clear();
  //cerr << "1" << endl;
  UpdateCorpusStatistics(sc, tStat);

  //cerr << "2" << endl;
  // Перебираем возможные варианты правил
  map<Rule, float> rules;
  //map<Rule, string> details;
  vector<Rule> rv;
  
  map<TagSet, TagStat>::const_iterator cit = tStat.begin();
  while (tStat.end() != cit) {
    if (cit->first.size() > 1) {
      // это омонимичный тег

      // LEFT
      map<TagSet, size_t>::const_iterator pC = cit->second.leftTag.begin();
      while (cit->second.leftTag.end() != pC) {
        map<Tag, size_t> freq;
        map<Tag, size_t> incontext;
        map<Tag, float> inc2freq; // incontext[X] / freq[X];

        //stringstream dss;
        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);
          freq[*pT] = tStat[tsT].freq;

          incontext[*pT] = tStat[tsT].leftTag[pC->first];
          //if (dss.str().size() > 0) dss << " ";
          //dss << pT->str() << ":" << freq[*pT] << "/" << incontext[*pT];
          inc2freq[*pT] = float(incontext[*pT]) / float(freq[*pT]);

          pT++;
        }

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY);
        if (bestScore > 0) {
          Rule r(cit->first, bestY, Context(-1, pC->first)); 
          rules[r] = bestScore;
          //map<TagSet, size_t>::const_iterator i = cit->second.leftTag.find(pC->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second << " : " << dss.str();
          //details[r] = ss.str();
          rv.push_back(r);
        }

        pC++;
      }

      // LEFT WORD
      map<string, size_t>::const_iterator pCW = cit->second.leftWord.begin();
      while (cit->second.leftWord.end() != pCW) {
        if (pCW->second < 3) { pCW++; continue; }
        map<Tag, size_t> freq;
        map<Tag, size_t> incontext;
        map<Tag, float> inc2freq;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);
          freq[*pT] = tStat[tsT].freq;

          incontext[*pT] = tStat[tsT].leftWord[pCW->first];
          inc2freq[*pT] = float(incontext[*pT]) / float(freq[*pT]);

          pT++;
        }

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY);
        if (bestScore > 0) {
          Rule r(cit->first, bestY, Context(-1, pCW->first)); 
          rules[r] = bestScore;
          //map<string, size_t>::const_iterator i = cit->second.leftWord.find(pCW->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second;// << " : " << dss.str();
          //details[r] = ss.str();
          rv.push_back(r);
        }
        
        pCW++;
      }

      // RIGHT
      pC = cit->second.rightTag.begin();
      while (cit->second.rightTag.end() != pC) {
        map<Tag, size_t> freq;
        map<Tag, size_t> incontext;
        map<Tag, float> inc2freq; // incontext[X] / freq[X];

        //stringstream dss;
        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);
          freq[*pT] = tStat[tsT].freq;

          incontext[*pT] = tStat[tsT].rightTag[pC->first];
          //if (dss.str().size() > 0) dss << " ";
          //dss << pT->str() << ":" << freq[*pT] << "/" << incontext[*pT];   
          inc2freq[*pT] = float(incontext[*pT]) / float(freq[*pT]);

          pT++;
        }

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY);
        if (bestScore > 0) {
          Rule r(cit->first, bestY, Context(+1, pC->first)); 
          rules[r] = bestScore;
          //map<TagSet, size_t>::const_iterator i = cit->second.rightTag.find(pC->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second << " : " << dss.str();
          //details[r] = ss.str();
          rv.push_back(r);
        }

        pC++;
      }

      // RIGHT WORD
      pCW = cit->second.rightWord.begin();
      while (cit->second.rightWord.end() != pCW) {
        if (pCW->second < 3) { pCW++; continue; }
        map<Tag, size_t> freq;
        map<Tag, size_t> incontext;
        map<Tag, float> inc2freq;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);
          freq[*pT] = tStat[tsT].freq;

          incontext[*pT] = tStat[tsT].rightWord[pCW->first];
          inc2freq[*pT] = float(incontext[*pT]) / float(freq[*pT]);

          pT++;
        }

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY);
        if (bestScore > 0) {
          Rule r(cit->first, bestY, Context(1, pCW->first)); 
          rules[r] = bestScore;
          //map<string, size_t>::const_iterator i = cit->second.rightWord.find(pCW->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second;// << " : " << dss.str();
          //details[r] = ss.str();
          rv.push_back(r);
        }
        
        pCW++;
      }

    }

    cit++;
  }

  //cerr << "3" << endl;
  less_by_second<Rule> lbs(rules);
  sort(rv.begin(), rv.end(), lbs);

/*
  cerr << "RULES:" << endl;
  for (size_t i = 0; i < rv.size(); i++) {
    cerr << rv[i].str() << " # " << rules[rv[i]] << endl; // << " " << details[rv[i]] << endl;
  }
  cerr << endl;
  return 0;
*/

  if (rv.size() > 0) {
    cerr << rv[0].str() << " # " << rules[rv[0]] << endl;

    ApplyRule(sc, rv[0]);
    knownRules.push_back(rv[0]);
    return rules[rv[0]];
  }

  return 0;

  // TODO: сделать тип struct Rule
  // TODO: сгенерировать список правил и выбрать лучшее
  // Rule bestRule; 
  // FindBestRule(tStat, bestRule); // прототип функции void FindBestRule(const map<TagSet, TagStat> &tStat, Rule &rule);
 
  // TODO: применить это лучшее правило к корпусу
  // ApplyRule(sc, bestRule); // void ApplyRule(SentenceCollection &sc, const Rule &rule);
  
  // TODO: сложить правило в какой-нибудь list<Rule>
 
}

void ApplyRule(SentenceCollection &sc, const Rule &rule) {
  SentenceCollection::iterator it = sc.begin();
  while (sc.end() != it) {
    for (size_t i = 0; i < it->size()-1; i++) {
      if (it->getToken(i).getPOST() == rule.from && rule.c.match(*it, i)) {
        //cerr << "Applying rule \"" << rule.str() << "\":" << endl;
        //cerr << "BEFORE: " << it->getToken(i).str() << endl;
        it->getNonConstToken(i).deleteAllButThis(rule.to);
        //cerr << "AFTER:  " << it->getToken(i).str() << endl << endl;
      }
    }
    it++;
  }
}

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat) {
  SentenceCollection::const_iterator cit = sc.begin();
  while (sc.end() != cit) {
    for (size_t i = 1; i < cit->size()-1; i++) {
      TagSet POST = cit->getToken(i).getPOST();
      tStat[POST].freq += 1;
      TagStat& r = tStat[POST];
      r.leftTag[cit->getToken(i-1).getPOST()] += 1;
      r.rightTag[cit->getToken(i+1).getPOST()] += 1;
      r.leftWord[cit->getToken(i-1).getText()] += 1;
      r.rightWord[cit->getToken(i+1).getText()] += 1;
    }
    
    cit++;
  }
  return;
  map<TagSet, TagStat>::const_iterator mcit = tStat.begin();
  while (tStat.end() != mcit) {
    cout << mcit->first.str() << '\t' << mcit->second.str() << endl;
    mcit++;
  } 
}

string toString(const map<TagSet, size_t> &m) {
  map<TagSet, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first.str() << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

string toString(const map<string, size_t> &m) {
  map<string, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

string TagStat::str() const {
  stringstream ss;
  ss << "freq = " << freq << endl;
  ss << "leftTag:" << endl << toString(leftTag) << endl;
  ss << "rightTag:" << endl << toString(rightTag) << endl; 
  ss << "leftWord:" << endl << toString(leftWord) << endl; 
  ss << "rightWord:" << endl << toString(rightWord) << endl;
  ss << "-----------------" << endl; 

  return ss.str();
}

