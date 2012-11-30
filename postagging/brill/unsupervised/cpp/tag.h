#include <string>
#include <sstream>
#include <set>

// TODO: remove dependecy from "iostream"
#include <iostream>

#include "utils.h"

#ifndef __TAG_H
#define __TAG_H

struct Tag {
  int v;

public:
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
};

inline bool operator<(const Tag& a, const Tag& b) {
  return a.v < b.v;
}

class TagSet {
  std::set<Tag> s;

public:
  TagSet(const std::string &str) {
    std::vector<std::string> v;
    split(str, ' ', v);
    for (size_t i = 0; i < v.size(); i++) {
      Tag t(v[i]);
      s.insert(t);
    }    
  }

  bool hasTag(const Tag t) {
    return false;
  }

  void insert(const Tag t) {
    s.insert(t);
  }

  size_t size() const { return s.size(); }

  Tag getPOST() const {
    Tag POSTag("ERRR");
    std::set<Tag>::const_iterator cit = s.begin();
    while (s.end() != cit) {
      if (cit->isPOST())
        POSTag = *cit;
      cit++;
    }

    if ("ERRR" == POSTag.str()) throw;
    return POSTag;
  }


  std::string str() const {
    std::set<Tag>::const_iterator cit = s.begin();
    std::string r; size_t i = 0;
    while (s.end() != cit) {
      r += cit->str(); i++;
      if (i < s.size()) r += ' ';
      cit++;
    }
    return r;
  }
};

inline bool operator<(const TagSet& a, const TagSet& b) {
  return a.str() < b.str();
}

class MorphInterp : public TagSet {
  unsigned int lemmaId;

public:
  MorphInterp(unsigned int id, const std::string &str) : TagSet(str), lemmaId(id) {
  }

  unsigned int getLemmaId() const { return lemmaId; }
};

#endif
