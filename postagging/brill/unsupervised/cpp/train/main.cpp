#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <list>
#include <set>
#include <map>
#include <string>
#include <algorithm>
#include <tr1/unordered_map>
#include <tr1/unordered_set>

#include "tag.h"
#include "token.h"
#include "sentence.h"
#include "corpora_io.h"

#include "brill.h"
#include "corpus_stat.h"

#include "aux.h"

using namespace std;
using namespace std::tr1;

//#define APPLY_WITH_IDX
#define OPT_SKIP_LOWSCORE_RULES

/*
struct TagStat {
  size_t freq;
  //map<TagSet, size_t> leftTag;
  //map<TagSet, size_t> rightTag;
  KWiCNode<TagSet> leftTagContext;
  KWiCNode<TagSet> rightTagContext;

  bool needsUpdate; // word counts and ind need update
  KWiCNode<string> leftWordContext;
  KWiCNode<string> rightWordContext;

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

  size_t getFreq(const vector<pair<TagSet, signed int> > &v) {
    
  }
};
*/

struct TrainingOptions {
  size_t leftContextSize;
  size_t rightContextSize;

  TrainingOptions()
    : leftContextSize(2), rightContextSize(2) { }
};

//void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat, const TrainingOptions &opt);
float DoOneStep(SentenceCollection &sc, const CorpusStat& stat, list<Rule>& knownRules, const TrainingOptions &opt); 
size_t ApplyRule(SentenceCollection &sc, const Rule &rule, bool reallyApply);

TrainingOptions options;

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
    //map<TagSet, TagStat> tagStat;

    readCorpus(argv[i], currentCorpus);
    cout << argv[i] << endl;

    //CorpusStat stat(currentCorpus, options.leftContextSize, options.rightContextSize, options.leftContextSize, options.rightContextSize);
    /*cout << stat.toString() << endl;

    Context c1("2:tag=PNCT & 1:tag=NOUN");
    cout << "c1 == " << c1.str() << endl;
    TagSet t1("NOUN");
    cout << "getFreq(t1, c1) == " << stat.getFreq(t1, c1) << endl;
    return 0;*/ 

    //currentCorpus = originalCorpus;
    list<Rule> rules;

    // TODO: делать это в цикле до тех пор, пока годных правил не останется
    CorpusStat stat(currentCorpus, options.leftContextSize, options.rightContextSize, options.leftContextSize, options.rightContextSize);

   float score = 0;
    do {
      //cerr << stat.toString() << endl;
      score = DoOneStep(currentCorpus, stat, rules, options);
      stat.clear();
      stat.update();
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


float constructRule(const unordered_map<Tag, size_t>& freq, const unordered_map<Tag, size_t>& incontext, const unordered_map<Tag, float>& inc2freq, Tag &bestY, float fBestScore = 0) {
  float bestScore = 0;

  unordered_map<Tag, size_t>::const_iterator pY = freq.begin();
  while (freq.end() != pY) {
    unordered_map<Tag, size_t>::const_iterator inc_it = incontext.find(pY->first);

#ifdef OPT_SKIP_LOWSCORE_RULES
    if (inc_it->second < bestScore || inc_it->second < fBestScore) { pY++; continue; }
#endif
    // нет смысла досчитывать, т.к. score = inc_it->second - (что-то там)
    // и, следовательно, больше уже не станет

    unordered_map<Tag, size_t>::const_iterator pZ = freq.begin();
    float maxValue = 0;
    Tag R;

    while (freq.end() != pZ) {
      if (pY->first == pZ->first) {
        pZ++;
        continue;
      }
      
      unordered_map<Tag, float>::const_iterator i2f_it = inc2freq.find(pZ->first);      
      if (i2f_it->second > maxValue) {
        maxValue = i2f_it->second;
        R = pZ->first;
      } 
 
      pZ++;
    }

    unordered_map<Tag, size_t>::const_iterator f_it = freq.find(pY->first);
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

string toString(const vector<set<Condition> > &v) {
  stringstream ss;
  for (size_t i = 0; i < v.size(); i++) {
    set<Condition>::const_iterator cit = v[i].begin();
    while (v[i].end() != cit) {
      ss << "[" << i << "] = " << cit->str() << endl;
      cit++;
    }
  }
  return ss.str();
}

namespace std { namespace tr1 {
template <>
struct hash<unordered_set<size_t> > {
public:
  inline size_t operator()(const unordered_set<size_t> &x) const throw() {
    size_t h = 0;
 
    unordered_set<size_t>::const_iterator cit = x.begin();
    while (x.end() != cit) {
      h = h ^ hash<int>()(*cit);
      cit++;
    }
   
    return h;
  }
};
} }

namespace std { namespace tr1 {
inline bool operator==(const std::tr1::unordered_set<size_t> &a, const std::tr1::unordered_set<size_t> &b) {
  if (a.size() != b.size()) return false;

  std::tr1::unordered_set<size_t>::const_iterator cit = a.begin();
  while (a.end() != cit) {
    if (b.end() == b.find(*cit))
      return false;
    cit++;
  }
 
  return true;
}
} }

template <class E>
class PermutationGenerator {

  size_t len;
  unordered_set<unordered_set<size_t> > lst;

  // result
  list<unordered_set<E> > res;

  size_t get_pos_list(unordered_set<unordered_set<size_t> > &l, unordered_set<size_t> &v, size_t start, size_t limit, size_t num_pos) {
    size_t c = 0;
    for (size_t i = start; i < limit; i++) {
      unordered_set<size_t> t = v;
      //if (t.size() > 0 && i == t[t.size()-1]) continue;
      t.insert(i);
      if (t.size() < num_pos && start < limit) {
        size_t nc = get_pos_list(l, t, start + 1, limit, num_pos);
        c += nc;
        if (nc <= 1) return c;
      } else {
        l.insert(t);
        c++;
      }
    }
    return c;
  }

  bool get_impl(const vector<unordered_set<E> > &data, unordered_set<E> &s, const unordered_set<size_t> &v, unordered_set<size_t>::const_iterator n) {
    typename unordered_set<E>::const_iterator cit = data[*n].begin();
    while (data[*n].end() != cit) {
      unordered_set<E> t = s;
      t.insert(*cit);
      unordered_set<size_t>::const_iterator z = n;
      z++;
      if (z == v.end()) {
        res.push_back(t);
      } else {
        get_impl(data, t, v, z);
      }
     
      cit++;
    }

    return true;
  }

public:
  PermutationGenerator(size_t sz, size_t l)
    : len(l) {

   for (size_t c = 1; c <= len; c++) {
     unordered_set<size_t> v;
     get_pos_list(lst, v, 0, sz, c);
   }
  }

  void init(const vector<unordered_set<E> > &d) {
    res.clear();
    unordered_set<unordered_set<size_t> >::const_iterator cit = lst.begin();
    while (lst.end() != cit) {
      unordered_set<E> s;
      //for (set<size_t>::const_iterator i = cit->begin(); i != cit->end(); i++) cerr << *i << " "; cerr << endl;
      get_impl(d, s, *cit, cit->begin());
      cit++;
    }
  }

  bool get(unordered_set<E> &s) {
    if (res.size() > 0) {
      s = res.front();
      res.pop_front();
      return true;
    }
    return false;
  }
};

float DoOneStep(SentenceCollection &sc, const CorpusStat& stat, list<Rule>& knownRules, const TrainingOptions &opt) {

  // Перебираем возможные варианты правил
  float fBestScore = 0;
  vector<Rule> bestRules;
  bestRules.reserve(32);

  PermutationGenerator<Condition> pg(opt.leftContextSize + opt.rightContextSize + 1, 2);
  size_t searchSpaceSize = 0;
  unordered_map<TagSet, size_t>::const_iterator cit = stat.mapTagSetFreq.begin();
  while (stat.mapTagSetFreq.end() != cit) {
    if (cit->first.size() < 2) {
      // Это не омонимичный тег. А мы ищем омонимичные.
      cit++;
      continue;
    }
//    cerr << "TS = " << cit->first.str() << endl;

    unordered_map<Tag, size_t> freq;
    TagSet::const_iterator pT = cit->first.begin();
    while (cit->first.end() != pT) {
      // pT - это неомонимичный тег, на который мы будем заменять cit->first
      TagSet tsT(*pT);

      unordered_map<TagSet, size_t>::const_iterator fr_it = stat.mapTagSetFreq.find(tsT);
      if (stat.mapTagSetFreq.end() != fr_it) {
        freq[*pT] = fr_it->second;
      } else {
        freq[*pT] = 0;
        //cerr << "WARNING: no stat for tag \"" << pT->str() << "\"" << endl;
      }

      pT++;
    }

    // Перебираем возможные контексты (в которых встречается cit->first)
    unordered_map<TagSet, vector<unordered_set<Condition> > >::const_iterator it_ctx = stat.mapTagSet2Features.find(cit->first);
    if (stat.mapTagSet2Features.end() == it_ctx) {
      cit++;
      continue;
    }

    const vector<unordered_set<Condition> > &map_ctx = it_ctx->second; 
    //cerr << toString(map_ctx) << endl;
    pg.init(map_ctx);
    //cerr << "map_ctx.size() == " << map_ctx.size() << endl;
    unordered_set<Condition> s;
    while (pg.get(s)) {
      size_t x = 0;
      for (unordered_set<Condition>::const_iterator sit = s.begin(); s.end() != sit; sit++)
        x += (Condition::word == sit->what ? 1 : 0);
      if (x > 1) continue;
      Context ctx(s); searchSpaceSize += 1;
      //cerr << "ctx = " << ctx.str() << endl;

      unordered_map<Tag, size_t> incontext;
      unordered_map<Tag, float> inc2freq; // incontext[X] / freq[X];
      size_t maxIncontext = 0;

      TagSet::const_iterator pT = cit->first.begin();
      while (cit->first.end() != pT) {
        // pT - это неомонимичный тег, на который мы будем заменять *cit
        TagSet tsT(*pT);

        size_t TinC = stat.getFreq(tsT, ctx);
        incontext[*pT] = TinC;
        if (TinC > maxIncontext) maxIncontext = TinC;
        inc2freq[*pT] = float(TinC) / float(freq[*pT]);

        pT++;
      }

#ifdef OPT_SKIP_LOWSCORE_RULES
      if (fBestScore > maxIncontext) continue; 
#endif

      Tag bestY;
      float bestScore = constructRule(freq, incontext, inc2freq, bestY, fBestScore);

      if (bestScore >= fBestScore) {
        if (bestScore > fBestScore) {
          fBestScore = bestScore;
          bestRules.clear();
        }

        bestRules.push_back(Rule(cit->first, bestY, ctx));
      } 
    }

    cit++;
  }

  cerr << "searchSpaceSize " << searchSpaceSize << endl;
 
  if (fBestScore > 0) {
    less_by_context_size lbss;
    sort(bestRules.begin(), bestRules.end(), lbss);

    size_t iMaxApplied = 0;
    size_t iMaxAppliedPos = 0;

    for (size_t i = 0; i < bestRules.size(); ++i) {
      Rule &r = bestRules[i];
      size_t n = ApplyRule(sc, r, false);

      if (n > iMaxApplied) {
        iMaxApplied = n;
        iMaxAppliedPos = i;
      }

      if (n < 1 && 0 == i) {
        cerr << "ERROR: rule (" << r.str() << ") found but can't be applied. Can't continue." << endl;
        throw;
      }
      stringstream ss;
      ss << "score=" << fBestScore << " applied=" << n ; //<< " fromfreq=" << tStat[r.from].freq;
      if (bestRules.size() > 1)
        ss << " gpos=" << i;
      r.add_comment(ss.str()); // начиная с этого места правило изменилось и не будет искаться в map
    
      cout << r.str() << endl;
      //knownRules.push_back(r);
      //break; // временно отключаем повторы
    }

    Rule &r = bestRules[iMaxAppliedPos];
    cout << "-> [" << iMaxAppliedPos << "] " << r.str() << endl;
    ApplyRule(sc, r, true);
    knownRules.push_back(r);

    return fBestScore;
  }

  return 0;
}


size_t ApplyRule(SentenceCollection &sc, const Rule &rule, bool reallyApply) {
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
        if (reallyApply)
	  it->getNonConstToken(i).deleteAllButThis(rule.to);
        //cerr << "AFTER:  " << it->getToken(i).str() << endl << endl;
        n++;
      }
    }
    it++;
  }
#endif

  //cerr << "RULE \"" << rule.str() << "\" applied " << n << " times" << endl;

  //tStat[rule.from].needsUpdate = true;
  //tStat[rule.to].needsUpdate = true;
  return n;
}

/*
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
*/
