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

#include "brill.h"

#include "aux.h"

using namespace std;

#define APPLY_WITH_IDX
#define OPT_SKIP_LOWSCORE_RULES

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

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat);
float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule>& knownRules); 
size_t ApplyRule(SentenceCollection &sc, const Rule &rule, map<TagSet, TagStat> &tStat);

int main(int argc, char **argv) {
  if (argc <= 1) {
    cerr << "corpus file is missing" << endl;
    return -1;
  }

#ifdef APPLY_WITH_IDX
  cerr << "# APPLY_WITH_IDX defined" << endl;
#endif

  for (int i = 1; i < argc; i++) {
    //SentenceCollection originalCorpus;
    SentenceCollection currentCorpus;
    map<TagSet, TagStat> tagStat;

    readCorpus(argv[i], currentCorpus);
    cout << argv[i] << endl;

    //currentCorpus = originalCorpus;
    list<Rule> rules;

    // TODO: делать это в цикле до тех пор, пока годных правил не останется
    float score = 0;
    do {
      score = DoOneStep(currentCorpus, tagStat, rules);
    } while (score > 0);

    //cerr << PrintRules(rules);

    //ofstream f_orig((string(argv[i]) + ".orig").c_str());
    //f_orig << PrintSC(originalCorpus) << endl;

    ofstream f_final((string(argv[i]) + ".final").c_str());
    //cerr << "FINAL:" << endl;
    f_final << PrintSC(currentCorpus);

    ofstream f_rules((string(argv[i]) + ".rules").c_str());
    f_rules << PrintRules(rules);

    cout << rules.size() << " rules" << endl;
  }

  return 0;
}

float constructRule(const map<Tag, size_t>& freq, const map<Tag, size_t>& incontext, const map<Tag, float>& inc2freq, Tag &bestY, float fBestScore = 0) {
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

float DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat, list<Rule> &knownRules) {
  UpdateCorpusStatistics(sc, tStat);

  // Перебираем возможные варианты правил
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
        size_t maxIncontext = 0;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);

          size_t TinC = tStat[tsT].leftTag[pC->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
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
        } 

        pC++;
      }

      // LEFT WORD
      map<string, size_t>::const_iterator pCW = cit->second.leftWord.begin();
      while (cit->second.leftWord.end() != pCW) {
        //if (pCW->second < 3) { pCW++; continue; }
        size_t maxIncontext = 0;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);

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
        }
        
        pCW++;
      }

      // RIGHT
      pC = cit->second.rightTag.begin();
      while (cit->second.rightTag.end() != pC) {
        size_t maxIncontext = 0;

        //stringstream dss;
        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          // pT - это неомонимичный тег, на который мы будем заменять *cit
          TagSet tsT(*pT);

          size_t TinC = tStat[tsT].rightTag[pC->first];
          incontext[*pT] = TinC;
          if (TinC > maxIncontext) maxIncontext = TinC;
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
        }

        pC++;
      }

      // RIGHT WORD
      pCW = cit->second.rightWord.begin();
      while (cit->second.rightWord.end() != pCW) {
        //if (pCW->second < 3) { pCW++; continue; }
        size_t maxIncontext = 0;

        TagSet::const_iterator pT = cit->first.begin();
        while (cit->first.end() != pT) {
          TagSet tsT(*pT);

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
        }
        
        pCW++;
      }
    }

    cit++;
  }

  if (fBestScore > 0) {
    less_by_from_freq<Rule> lbff(tStat);
    sort(bestRules.begin(), bestRules.end(), lbff);

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
    }

    return fBestScore;
  }

  return 0;
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
      TagSet POST = vPOST[i];

      TagStat& r = tStat[POST];
      r.freq += 1;
      //TagStat& r = tStat[POST];

      const Token& rleftToken = rsent.getToken(i-1);
      const Token& rrightToken = rsent.getToken(i+1);

      const TagSet& leftTS = vPOST[i-1];
      const TagSet& rightTS = vPOST[i+1];

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

