#include <string>
#include <map>
#include <list>

#include "tag.h"
#include "sentence.h"

std::string toString(const std::map<TagSet, size_t> &m);
std::string toString(const std::map<std::string, size_t> &m);

std::string PrintRules(const std::list<Rule>& lr);
std::string PrintSC(const SentenceCollection &sc);

template<class T>
struct less_by_second {
  std::map<T, float>& rmap;
  less_by_second(std::map<T, float>& _rmap) : rmap(_rmap) { }

  bool operator()(const T& a, const T& b) const {
    return rmap[a] > rmap[b];
  }
};

class TagStat;

template<class T>
struct less_by_from_freq {
  std::map<TagSet, TagStat>& rmap;
  less_by_from_freq(std::map<TagSet, TagStat>& _rmap) : rmap(_rmap) { }

  bool operator()(const T& a, const T& b) const {
    return rmap[a.from].freq > rmap[b.from].freq;
  }
};


