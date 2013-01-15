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

#define APPLY_WITH_IDX
#define OPT_SKIP_LOWSCORE_RULES

string toString(const map<TagSet, size_t> &m);
string toString(const map<string, size_t> &m);

struct TagStat {
  size_t freq;
  map<TagSet, size_t> leftTag;
  map<TagSet, size_t> rightTag;

  bool needsUpdate; // word counts and ind need update
  map<string, size_t> leftWord;
  map<string, size_t> rightWord;

  TagStat() : freq(0), needsUpdate(true) {
#ifdef APPLY_WITH_IDX
    idx.reserve(10000);
#endif
  }

#ifdef APPLY_WITH_IDX
  // index
  vector< pair<size_t, size_t> > idx;
#endif

  string str() const;
};

class Rule;

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat);
float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule>& knownRules); 
size_t ApplyRule(SentenceCollection &sc, const Rule &rule, map<TagSet, TagStat> &tStat);

string PrintRules(const list<Rule>& lr);
string PrintSC(const SentenceCollection &sc);

//map<TagSet, TagStat> tagStat;

int main(int argc, char **argv) {
  if (argc <= 1) {
    cerr << "corpus file is missing" << endl;
    return -1;
  }

#ifdef APPLY_WITH_IDX
  cerr << "# APPLY_WITH_IDX defined" << endl;
#endif

  for (int i = 1; i < argc; i++) {
    SentenceCollection originalCorpus;
    SentenceCollection currentCorpus;
    map<TagSet, TagStat> tagStat;

    //tagStat.clear();

    readCorpus(argv[i], originalCorpus);
    cout << argv[i] << endl;

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
      ss << pos << ":" << "tag" << "=" << value.str(true);
    else if (word == what)
      ss << pos << ":" << "word" << "=" << form;
    return ss.str();
  }
};

inline bool operator<(const Condition& a, const Condition& b) {
  if (a.pos < b.pos) return true;
  else if (a.pos > b.pos) return false;

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
  string comments;

public:
  Rule()
    : from(T(UNKN)), to(T(UNKN)), c(Context(0, T(UNKN))) { }

  Rule(const TagSet& _from, const Tag& _to, const Context& _c)
    : from(_from), to(_to), c(_c) { }

  string str(bool bNoComments = false) const {
    stringstream ss;
    ss << from.str(true) << " -> " << to.str() << " | " << c.str();
    if (!bNoComments && comments.size() > 0)
      ss << " # " << comments;
    return ss.str();
  }

  void add_comment(const string &s) {
    if (comments.size() > 0)
      comments += "; ";
    comments += s;
  }
};

inline bool operator<(const Rule& a, const Rule& b) {
/*  if (a.from < b.from) return true;
  else if (b.from < a.from) return false;*/

  return a.str(true) < b.str(true);
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

float constructRule(const map<Tag, size_t>& freq, const map<Tag, size_t>& incontext, const map<Tag, float>& inc2freq, Tag &bestY, float fBestScore = 0) {

  //Tag bestY;
  float bestScore = 0;

  map<Tag, size_t>::const_iterator pY = freq.begin();
  while (freq.end() != pY) {
    map<Tag, size_t>::const_iterator inc_it = incontext.find(pY->first);

#ifdef OPT_SKIP_LOWSCORE_RULES
    if (inc_it->second < bestScore /*|| inc_it->second < fBestScore*/) { pY++; continue; }
#endif
    // нет смысла досчитывать, т.к. score = inc_it->second - (что-то там)
    // и, следовательно, больше уже не станет

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
    //map<Tag, size_t>::const_iterator inc_it = incontext.find(pY->first);
    float score = inc_it->second - f_it->second * maxValue;

    if (score > bestScore) {
      bestScore = score;
      bestY = pY->first;
    }
          
    pY++;
  }

  return bestScore;
}

template<class T>
struct less_by_from_freq {
  map<TagSet, TagStat>& rmap;
  less_by_from_freq(map<TagSet, TagStat>& _rmap) : rmap(_rmap) { }

  bool operator()(const T& a, const T& b) const {
    return rmap[a.from].freq > rmap[b.from].freq;
  }
};

float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule> &knownRules) {
  //tStat.clear();
  //cerr << "1" << endl;
  UpdateCorpusStatistics(sc, tStat);

  //cerr << "2" << endl;
  // Перебираем возможные варианты правил
  //map<Rule, float> rules;
  //map<Rule, string> details;
  //vector<Rule> rv;
  float fBestScore = 0;
  vector<Rule> bestRules;
  bestRules.reserve(32);
  
  map<TagSet, TagStat>::const_iterator cit = tStat.begin();
  while (tStat.end() != cit) {
    if (cit->first.size() > 1 /*&& cit->second.freq > 0*/) {
      // это омонимичный тег

      map<Tag, size_t> freq;
      TagSet::const_iterator pT = cit->first.begin();
      while (cit->first.end() != pT) {
        // pT - это неомонимичный тег, на который мы будем заменять *cit
        TagSet tsT(*pT);
        freq[*pT] = tStat[tsT].freq;
        pT++;
      }

      map<Tag, size_t> incontext;
      map<Tag, float> inc2freq; // incontext[X] / freq[X];
 

      // LEFT
      map<TagSet, size_t>::const_iterator pC = cit->second.leftTag.begin();
      while (cit->second.leftTag.end() != pC) {
        //map<Tag, size_t> freq;
        //map<Tag, size_t> incontext;
        //map<Tag, float> inc2freq; // incontext[X] / freq[X];
        size_t maxIncontext = 0;

        //stringstream dss;
        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);
          //freq[*pT] = tStat[tsT].freq;

          size_t TinC = tStat[tsT].leftTag[pC->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
          //if (dss.str().size() > 0) dss << " ";
          //dss << pT->str() << ":" << freq[*pT] << "/" << incontext[*pT];
          inc2freq[*pT] = float(TinC) / float(freq[*pT]);

          pT++;
        }

#ifdef OPT_SKIP_LOWSCORE_RULES
        if (fBestScore > maxIncontext) { pC++; continue; }
#endif

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY, fBestScore);

        if (bestScore >= fBestScore) {
          if (bestScore > fBestScore) {
            fBestScore = bestScore;
            bestRules.clear();
          }

          bestRules.push_back(Rule(cit->first, bestY, Context(-1, pC->first)));

          //map<TagSet, size_t>::const_iterator i = cit->second.leftTag.find(pC->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second << " : " << dss.str();
          //details[r] = ss.str();
          //rv.push_back(r);
        } 

        pC++;
      }

      // LEFT WORD
      map<string, size_t>::const_iterator pCW = cit->second.leftWord.begin();
      while (cit->second.leftWord.end() != pCW) {
        //if (pCW->second < 3) { pCW++; continue; }
        //map<Tag, size_t> freq;
        //map<Tag, size_t> incontext;
        //map<Tag, float> inc2freq;
        size_t maxIncontext = 0;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);
          //freq[*pT] = tStat[tsT].freq;

          size_t TinC  = tStat[tsT].leftWord[pCW->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
          inc2freq[*pT] = float(TinC) / float(freq[*pT]);

          pT++;
        }

#ifdef OPT_SKIP_LOWSCORE_RULES
        if (fBestScore > maxIncontext) { pCW++; continue; }
#endif

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY, fBestScore);
        if (bestScore >= fBestScore) {
          if (bestScore > fBestScore) {
            fBestScore = bestScore;
            bestRules.clear();
          }

          bestRules.push_back(Rule(cit->first, bestY, Context(-1, pCW->first))); 

          //map<string, size_t>::const_iterator i = cit->second.leftWord.find(pCW->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second;// << " : " << dss.str();
          //details[r] = ss.str();
          //rv.push_back(r);
        }
        
        pCW++;
      }

      // RIGHT
      pC = cit->second.rightTag.begin();
      while (cit->second.rightTag.end() != pC) {
        //map<Tag, size_t> freq;
        //map<Tag, size_t> incontext;
        //map<Tag, float> inc2freq; // incontext[X] / freq[X];
        size_t maxIncontext = 0;

        //stringstream dss;
        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);
          //freq[*pT] = tStat[tsT].freq;

          size_t TinC = tStat[tsT].rightTag[pC->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
          //if (dss.str().size() > 0) dss << " ";
          //dss << pT->str() << ":" << freq[*pT] << "/" << incontext[*pT];   
          inc2freq[*pT] = float(TinC) / float(freq[*pT]);

          pT++;
        }

#ifdef OPT_SKIP_LOWSCORE_RULES
        if (fBestScore > maxIncontext) { pC++; continue; }
#endif

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY, fBestScore);
        if (bestScore >= fBestScore) {
          if (bestScore > fBestScore) {
            fBestScore = bestScore;
            bestRules.clear();
          }

          bestRules.push_back(Rule(cit->first, bestY, Context(+1, pC->first))); 
          //map<TagSet, size_t>::const_iterator i = cit->second.rightTag.find(pC->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second << " : " << dss.str();
          //details[r] = ss.str();
          //rv.push_back(r);
        }

        pC++;
      }

      // RIGHT WORD
      pCW = cit->second.rightWord.begin();
      while (cit->second.rightWord.end() != pCW) {
        //if (pCW->second < 3) { pCW++; continue; }
        //map<Tag, size_t> freq;
        //map<Tag, size_t> incontext;
        //map<Tag, float> inc2freq;
        size_t maxIncontext = 0;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);
          //freq[*pT] = tStat[tsT].freq;

          size_t TinC = tStat[tsT].rightWord[pCW->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
          inc2freq[*pT] = float(TinC) / float(freq[*pT]);

          pT++;
        }

#ifdef OPT_SKIP_LOWSCORE_RULES
        if (fBestScore > maxIncontext) { pCW++; continue; }
#endif

        Tag bestY;
        float bestScore = constructRule(freq, incontext, inc2freq, bestY, fBestScore);
        if (bestScore >= fBestScore) {
          if (bestScore > fBestScore) {
            fBestScore = bestScore;
            bestRules.clear();
          }

          bestRules.push_back(Rule(cit->first, bestY, Context(1, pCW->first))); 
          //map<string, size_t>::const_iterator i = cit->second.rightWord.find(pCW->first);
          //stringstream ss; ss << tStat[cit->first].freq << "/" << i->second;// << " : " << dss.str();
          //details[r] = ss.str();
          //rv.push_back(r);
        }
        
        pCW++;
      }

    }

    cit++;
  }

  //cerr << "3" << endl;
  //less_by_second<Rule> lbs(rules);
  //sort(rv.begin(), rv.end(), lbs);

/*
  cerr << "RULES:" << endl;
  for (size_t i = 0; i < 10; i++) { //rv.size(); i++) {
    cerr << "R " << rv[i].str() << " # " << rules[rv[i]] << endl; // << " " << details[rv[i]] << endl;
  }
  cerr << endl;
//  return 0;
*/

  if (/*rv.size()*/ fBestScore > 0) {
    less_by_from_freq<Rule> lbff(tStat);
    sort(bestRules.begin(), bestRules.end(), lbff);

//    string altFrom;
//    size_t nBestRule = 0;
//    size_t maxTSFreq = 0;
    for (size_t i = 0; i < bestRules.size(); ++i) {
      Rule &r = bestRules[i];
      size_t n = ApplyRule(sc, r, tStat);
      stringstream ss;
      ss << "score=" << fBestScore << " applied=" << n << " fromfreq=" << tStat[r.from].freq;
      if (bestRules.size() > 1)
        ss << " gpos=" << i;
      r.add_comment(ss.str()); // начиная с этого места правило изменилось и не будет искаться в map
    
      cerr << r.str() << endl;
      knownRules.push_back(r);
      //break; // временно отключаем повторы

/*      if (tStat[bestRules[i].from].freq > maxTSFreq) {
        nBestRule = i;
        maxTSFreq = tStat[bestRules[i].from].freq;
      }*/
    }

/*    for (size_t i = 0; i < bestRules.size(); ++i)
      if (i != nBestRule)
        altFrom += bestRules[i].from.str(true) + " / ";*/
/*
    Rule &bestRule = bestRules[nBestRule];

    size_t n = ApplyRule(sc, bestRule, tStat);
    ss << "score=" << fBestScore << " applied=" << n << " freq(from)=" << maxTSFreq;
    bestRule.add_comment(ss.str()); // начиная с этого места правило изменилось и не будет искаться в map
    
    cerr << bestRule.str() << endl;

    knownRules.push_back(bestRule);*/

    return fBestScore;
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

size_t ApplyRule(SentenceCollection &sc, const Rule &rule, map<TagSet, TagStat> &tStat) {
  size_t n = 0;

#ifdef APPLY_WITH_IDX
  map<TagSet, TagStat>::const_iterator cit = tStat.find(rule.from);
  if (tStat.end() == cit) throw;
  const TagStat& r = cit->second;

  //cerr << "IDX size for \"" << rule.from.str() << "\" is " << r.idx.size() << endl;
  for (size_t i = 0; i < r.idx.size(); ++i) {
    Sentence &rs = sc[r.idx[i].first];

    if (rule.c.match(rs, r.idx[i].second) && rs.getToken(r.idx[i].second).getPOST() == rule.from) {
      // вторая проверка нужна на случай, если это не первое правило в группе и предыдущие
      // уже изменили этот токен ... может быть стоит отмечать такие токены в индексе?
      rs.getNonConstToken(r.idx[i].second).deleteAllButThis(rule.to);
      n++;
    }
  }
#else
  SentenceCollection::iterator it = sc.begin();
  while (sc.end() != it) {
    for (size_t i = 0; i < it->size()-1; i++) {
      if (it->getToken(i).getPOST() == rule.from && rule.c.match(*it, i)) {
        //cerr << "Applying rule \"" << rule.str() << "\":" << endl;
        //cerr << "BEFORE: " << it->getToken(i).str() << endl;
        it->getNonConstToken(i).deleteAllButThis(rule.to);
        //cerr << "AFTER:  " << it->getToken(i).str() << endl << endl;
        n++;
      }
    }
    it++;
  }
#endif

  //cerr << "RULE \"" << rule.str() << "\" applied " << n << " times" << endl;

  tStat[rule.from].needsUpdate = true;
  tStat[rule.to].needsUpdate = true;
  return n;
}

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat) {
  // удаляем устаревшую статистику
  vector<TagSet> sNeedUpdate;
  sNeedUpdate.reserve(16);
  map<TagSet, TagStat>::iterator it = tStat.begin();
  while (tStat.end() != it) {
    it->second.leftTag.clear();
    it->second.rightTag.clear();
    //it->second.needsUpdate=true;

    if (it->second.needsUpdate) {
      sNeedUpdate.push_back(it->first);

      it->second.leftWord.clear();
      it->second.rightWord.clear();

#ifdef APPLY_WITH_IDX
      it->second.idx.reserve(it->second.freq);
      it->second.idx.clear();
#endif
    }

    it->second.freq = 0;

    it++;
  }

  //SentenceCollection::const_iterator cit = sc.begin();
  //  while (sc.end() != cit) {
  for (size_t sid = 0; sid < sc.size(); sid++) {
    const Sentence& rsent = sc[sid];
    vector<TagSet> vPOST;
    vPOST.resize(rsent.size());

    for (size_t j = 0; j < rsent.size(); j++)
      vPOST[j] = rsent.getToken(j).getPOST();

    for (size_t i = 1; i < rsent.size()-1; i++) {
      TagSet POST = vPOST[i]; //rsent.getToken(i).getPOST();

      TagStat& r = tStat[POST];
      r.freq += 1;
      //TagStat& r = tStat[POST];

      const Token& rleftToken = rsent.getToken(i-1);
      const Token& rrightToken = rsent.getToken(i+1);

      const TagSet& leftTS = vPOST[i-1]; //rleftToken.getPOST();
      const TagSet& rightTS = vPOST[i+1]; //rrightToken.getPOST();

      r.leftTag[leftTS] += 1;
      r.rightTag[rightTS] += 1;

      if (r.needsUpdate) {
        //cerr << "UPD: \"" << POST.str() << "\" needs update" << endl;

        if (!leftTS.hasTag(T(SBEG)) )
          r.leftWord[rleftToken.getText()] += 1;

        if (!rightTS.hasTag(T(SEND)) )
          r.rightWord[rrightToken.getText()] += 1;

#ifdef APPLY_WITH_IDX
        if (POST.size() > 1) {
          // строим индексы только для омонимичных тегов
          pair<size_t, size_t> p;
          p.first = sid;
          p.second = i;
          r.idx.push_back(p);
        }
#endif
        //r.needsUpdate = false;
      }
    }
    
    //cit++;
  }

  vector<TagSet>::const_iterator cit = sNeedUpdate.begin();
  while (sNeedUpdate.end() != cit) {
    tStat[*cit].needsUpdate = false;
    cit++;
  }    

  return;

  map<TagSet, TagStat>::const_iterator mcit = tStat.begin();
  while (tStat.end() != mcit) {
    cout << mcit->first.str() << '\t' << mcit->second.str() << endl;
    mcit++;
  } 
  throw;
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

