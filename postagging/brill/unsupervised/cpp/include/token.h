#include <string>
#include <sstream>
#include <set>

#include "tag.h"

#ifndef __TOKEN_H
#define __TOKEN_H

class Token {
  std::string text;
  std::set<MorphInterp> var;

public:
  Token(const std::string &str, const std::set<MorphInterp> &v) : text(str), var(v) { }

  const std::string getText() const {
    return text;
  }

  TagSet getPOST() const {
    TagSet POSTagSet("");
    std::set<MorphInterp>::const_iterator cit = var.begin();
    while (var.end() != cit) {
      POSTagSet.insert(cit->getPOST());
      cit++;
    }

    return POSTagSet;
  }

  bool isWord() const {
    TagSet POST = getPOST();
    if (POST.hasTag(T(LATN)) || POST.hasTag(T(ROMN)) || POST.hasTag(T(PNCT)) || POST.hasTag(T(NUMB)))
      return false;
    return true;
  }

  const std::set<MorphInterp>& getMorph() const {
    return var;
  }

  /*void setMorph(const std::set<MorphInterp>& mi) {
    var = mi;
  }*/

  void deleteAllButThis(Tag t) {
    std::set<MorphInterp>::iterator it = var.begin();
    while (var.end() != it) {
      if (! it->hasTag(t)) {
        var.erase(it);
        it = var.begin();
      } else
          it++;
    }
  }

  std::string str() const {
    std::stringstream ss;
    ss << text << '\t';
    std::set<MorphInterp>::const_iterator cit = var.begin();
    size_t i = 0;
    while (var.end() != cit) {
      ss << cit->str();
      if (i < var.size()) ss << '\t';
      cit++;
    }
    return ss.str();
  }
};

#endif
