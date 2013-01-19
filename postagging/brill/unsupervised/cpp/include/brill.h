#include <string>
#include <sstream>
#include <set>

#include "tag.h"

#ifndef __BRILL_H
#define __BRILL_H

struct Condition {
  enum EType { tag, word };
  signed int pos;
  EType what;
  TagSet value;
  std::string form;

  Condition(signed int _pos, const TagSet& _value)
    : pos(_pos), what(tag), value(_value) { }

  Condition(signed int _pos, const std::string& _form)
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

  std::string str() const {
    std::stringstream ss;
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
  std::set<Condition> elements;

public:
  Context(signed int pos, const TagSet& value) {
    elements.insert(Condition(pos, value));
  }

  Context(signed int pos, const std::string& word) {
    elements.insert(Condition(pos, word));
  }

  inline bool match(const Sentence& s, size_t pos) const {
    std::set<Condition>::const_iterator cit = elements.begin();
    while (elements.end() != cit) {
      if (! cit->match(s, pos))
        return false;
      cit++;
    }
    return true;
  }

  std::string str() const {
    std::stringstream ss;
    std::set<Condition>::const_iterator cit = elements.begin();

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
  std::string comments;

public:
  Rule()
    : from(T(UNKN)), to(T(UNKN)), c(Context(0, T(UNKN))) { }

  Rule(const TagSet& _from, const Tag& _to, const Context& _c)
    : from(_from), to(_to), c(_c) { }

  std::string str(bool bNoComments = false) const {
    std::stringstream ss;
    ss << from.str(true) << " -> " << to.str() << " | " << c.str();
    if (!bNoComments && comments.size() > 0)
      ss << " # " << comments;
    return ss.str();
  }

  void add_comment(const std::string &s) {
    if (comments.size() > 0)
      comments += "; ";
    comments += s;
  }
};

inline bool operator<(const Rule& a, const Rule& b) {
  return a.str(true) < b.str(true);
}

#endif
