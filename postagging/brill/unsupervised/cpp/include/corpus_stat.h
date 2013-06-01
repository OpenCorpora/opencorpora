#ifndef __CORPUS_STAT_H
#define __CORPUS_STAT_H


#include <map>
#include <set>
#include <string>
#include <sstream>

#include "brill.h"
#include "corpora_io.h"

struct CorpusPos {
  size_t sentPos;
  size_t tokenPos;

  CorpusPos(size_t sp, size_t tp) : sentPos(sp), tokenPos(tp) { }

  std::string str() const {
    std::stringstream ss;
    ss << "(" << sentPos << "," << tokenPos << ")";
    return ss.str(); 
  }
};

inline bool operator<(const CorpusPos &cp1, const CorpusPos &cp2) {
  if (cp1.sentPos != cp2.sentPos)
    return cp1.sentPos < cp2.sentPos;
  
  return cp1.tokenPos < cp2.tokenPos;
}

class CorpusStat {
  std::map<Condition, std::set<CorpusPos> > entries;

  const SentenceCollection &sc;
  size_t leftTagContext;
  size_t rightTagContext;
  size_t leftWordContext;
  size_t rightWordContext;

  void generate();
  void calcFormFreq();

public:

  std::map<TagSet, size_t> mapTagSetFreq;
  std::map<std::string, size_t> mapFormFreq;
  std::map<TagSet, std::vector<std::set<Condition> > > mapTagSet2Features;

public:
  CorpusStat(const SentenceCollection &_sc, size_t _leftTagContext=1, size_t _rightTagContext=1, size_t _leftWordContext=1, size_t _rightWordContext=1)
    : sc(_sc), leftTagContext(_leftTagContext), rightTagContext(_rightTagContext), leftWordContext(_leftWordContext), rightWordContext(_rightWordContext) {
    calcFormFreq();
    generate();
  }

  void update() {
    generate();
  }
 
  void clear() {
    mapTagSetFreq.clear();
    mapTagSet2Features.clear();
  }

  size_t getFreq(const TagSet &ts, const Context &c) const;

  size_t getFreq(const Tag &t, const Context &c) const {
    TagSet tsT(t);
    return getFreq(tsT, c);
  }

  std::string toString() const;
};


#endif // CORPUS_STAT_H
