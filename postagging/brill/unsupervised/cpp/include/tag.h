#include <string>
#include <sstream>
#include <algorithm>
#include <vector>
#include <set>
#include <tr1/unordered_map>
#include <tr1/unordered_set>

// TODO: remove dependecy from "iostream"
#include <iostream>

#include "utils.h"

#ifndef __TAG_H
#define __TAG_H

struct Tag {
  int v;

public:
  Tag() : v(0) { }

  Tag(const std::string &str) {
    if (4 != str.size()) {
      std::cerr << "Bad grammeme: \"" << str << "\" of size " << str.size() << std::endl;
      throw;
    }
    v = *((int*)str.c_str());
  }

  bool isPOST() const {
    char *pch = (char*)(&v);
    int i = 0;
    while (i < 4) { 
      if (*pch < 'A' || *pch > 'Z')
        return false;
      pch++;
      i++;
    }
    return true;
  }

  inline std::string str() const {
    char ch[5];
    *((int*)ch) = v;
    ch[4] = 0;
    return ch;
  }

  inline int getInt() const {
    return v;
  }
};

inline bool operator<(const Tag& a, const Tag& b) {
  return a.v < b.v;
}

inline bool operator==(const Tag& a, const Tag& b) {
  return a.v == b.v;
}

namespace std { namespace tr1 {
template <>
struct hash<Tag> {
public:
  inline size_t operator()(const Tag &x) const throw() {
    return hash<int>()(hash<int>()(x.getInt()));
  }
};
} }


#define T(str) Tag(# str ) 

class TagSet {
//  typedef std::tr1::unordered_set<Tag> basic_set_t;
  typedef std::set<Tag> basic_set_t;

  basic_set_t s;

public:

  typedef basic_set_t::const_iterator const_iterator;
  inline const_iterator begin() const { return s.begin(); }
  inline const_iterator end() const { return s.end(); }

  TagSet() { }

  TagSet(const std::string &str) {
    std::vector<std::string> v;
    split(str, ' ', v);
    for (size_t i = 0; i < v.size(); i++) {
      if (4 != v[i].size()) throw;
      Tag t(v[i]);
      s.insert(t);
    }    
  }

  TagSet(const Tag &t) {
    s.insert(t);
  }

  bool hasTag(const Tag t) const {
    //std::cerr << "TagSet(\"" << str() << "\").hasTag(\"" << t.str() << "\")" << std::endl;
    if (s.end() == s.find(t))
      return false;
    return true;
  }

  void insert(const Tag t) {
    s.insert(t);
  }

  size_t size() const { return s.size(); }

  Tag getPOST() const {
    Tag POSTag("ERRR");
    basic_set_t::const_iterator cit = s.begin();
    while (s.end() != cit) {
      if (cit->isPOST())
        POSTag = *cit;
      cit++;
    }

    if ("ERRR" == POSTag.str()) throw;
    return POSTag;
  }

  virtual std::string str(bool bSortAlpha = false) const {
    std::string r;

    if (bSortAlpha) {
      std::vector<std::string> v;
      basic_set_t::const_iterator cit = s.begin();
      while (s.end() != cit) {
        v.push_back(cit->str());
        cit++;
      }
      size_t i = 0; 
      std::sort(v.begin(), v.end());
      std::vector<std::string>::const_iterator vit = v.begin();
      while (v.end() != vit) {
        r += *vit; i++;
        if (i < v.size()) r += ' ';
        vit++;
      }
    } else {
      basic_set_t::const_iterator cit = s.begin();
      size_t i = 0;
      while (s.end() != cit) {
        r += cit->str(); i++;
        if (i < s.size()) r += ' ';
        cit++;
      }
    }
    return r;
  }
};

inline bool operator<(const TagSet& a, const TagSet& b) {
  TagSet::const_iterator cita = a.begin();
  TagSet::const_iterator citb = b.begin();
  while (*cita == *citb) { 
    cita++;
    citb++;
 //   if (a.end() == cita || b.end() == citb) break;

    if (a.end() == cita) {
      if (b.end() != citb) return true;
      else return false;
    }
    if (b.end() == citb) return false;
  }

  return *cita < *citb;

  /*if (a.size() > b.size()) return true;
  else if (a.size() < b.size()) return false;
  else */
  //return a.str() < b.str();
}

inline bool operator==(const TagSet& a, const TagSet& b) {
  if (a.size() != b.size())
    return false;

  TagSet::const_iterator cit = a.begin();
  while (a.end() != cit) {
    if (!b.hasTag(*cit))
      return false;
    cit++;
  }

  return true;
}

namespace std { namespace tr1 {
template <>
struct hash<TagSet> {
public:
  inline size_t operator()(const TagSet &x) const throw() {
    size_t h = 0;
 
    TagSet::const_iterator cit = x.begin();
    while (x.end() != cit) {
      h = h ^ hash<Tag>()(*cit);
      cit++;
    }
   
    return h;
  }
};
} }

class MorphInterp : public TagSet {
  unsigned int lemmaId;

public:
  MorphInterp(unsigned int id, const std::string &str) : TagSet(str), lemmaId(id) {
    //std::cerr << "MorphInterp::MorphInterp(" << id << ", \"" << str << "\")" << std::endl;
  }

  unsigned int getLemmaId() const { return lemmaId; }

  virtual std::string str() const {
    std::stringstream ss;
    ss << lemmaId << " d " << TagSet::str();
    return ss.str();
  }
};

inline std::string toString(const std::set<MorphInterp> &s) {
  std::stringstream ss;
  std::set<MorphInterp>::const_iterator cit = s.begin();
  size_t i = 0;
  while (s.end() != cit) {
    ss << cit->str(); i++;
    if (i < s.size()) ss << '\t';
    cit++;
  }
  return ss.str();
}

#endif
