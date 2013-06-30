#include <string>
#include <sstream>
#include <set>
#include <tr1/unordered_map>
#include <tr1/unordered_set>

#include "tag.h"
#include "sentence.h"

#ifndef __BRILL_H
#define __BRILL_H

//namespace std { namespace tr1 { } }

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

  Condition(const std::string &s) {
    parse(s);
  }

  inline bool match(const Sentence& s, size_t _pos) const {
    if (_pos + pos < 0 || _pos + pos > s.size() - 1)
      return false;

    if (tag == what && s.getToken(pos + _pos).getPOST() == value)
      return true;
    else if (word == what && s.getToken(pos + _pos).getText() == form)
      return true;

    return false;
  }

  inline std::string str() const {
    std::stringstream ss;
    if (tag == what)
      ss << pos << ":" << "tag" << "=" << value.str(true);
    else if (word == what)
      ss << pos << ":" << "word" << "=" << form;
    return ss.str();
  }

  void parse(const std::string &s);
};

inline bool operator<(const Condition& a, const Condition& b) {
  if (a.what < b.what) return true;
  else if (a.what > b.what) return false;

  if (a.pos < b.pos) return true;
  else if (a.pos > b.pos) return false;
 
  if (Condition::tag == a.what) {
      if (a.value < b.value) return true;
      else /*if (a.value => b.value)*/ return false;
  } else if (Condition::word == a.what) {
      if (a.form < b.form) return true;
      else /*if (a.form => b.form)*/ return false;
  }

  return a.str() < b.str();
}

inline bool operator==(const Condition& a, const Condition& b) {
  return (a.what == b.what) && (a.pos == b.pos) && (Condition::tag == a.what ? a.value == b.value : a.form == b.form);
}

namespace std { namespace tr1 {
template <>
struct hash<Condition> {
public:
  size_t operator()(const Condition &x) const throw() {
    return hash<int>()((int)(x.what)) ^ hash<int>()(x.pos) ^ (Condition::tag == x.what ? hash<TagSet>()(x.value) : hash<std::string>()(x.form));
  }
};
} }

class Context {
  std::set<Condition> elements;

  void parse(const std::string &s);

public:
  Context(signed int pos, const TagSet& value) {
    elements.insert(Condition(pos, value));
  }

  Context(signed int pos, const std::string& word) {
    elements.insert(Condition(pos, word));
  }

  Context(const std::string &s) {
    parse(s);
  }

  Context(const std::set<Condition> &sc) {
    std::set<Condition>::const_iterator cit = sc.begin();
    while (sc.end() != cit) {
      elements.insert(*cit);
      cit++;
    }
  }

  Context(const std::tr1::unordered_set<Condition> &sc) {
    std::tr1::unordered_set<Condition>::const_iterator cit = sc.begin();
    while (sc.end() != cit) {
      elements.insert(*cit);
      cit++;
    }
  }

  size_t size() const { return elements.size(); }

  inline bool match(const Sentence& s, size_t pos) const {
    std::set<Condition>::const_iterator cit = elements.begin();
    while (elements.end() != cit) {
      if (! cit->match(s, pos))
        return false;
      cit++;
    }
    return true;
  }

  std::set<Condition>::const_iterator begin() const {
    return elements.begin();
  }

  std::set<Condition>::const_iterator end() const {
    return elements.end();
  }

  inline std::string str() const {
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

  inline std::string str(bool bNoComments = false) const {
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

  friend class less_by_context_size;
};

inline bool operator<(const Rule& a, const Rule& b) {
  return a.str(true) < b.str(true);
}

inline size_t countTags(const Context &c) {
  size_t r = 0;
  std::set<Condition>::const_iterator cit = c.begin();
  while (c.end() != cit) {
    if (Condition::tag == cit->what)
      r++;
    cit++;
  }
  return r;
}

struct less_by_context_size {
  bool operator()(const Rule& a, const Rule& b) const {
    if (a.c.size() == b.c.size()) {
      size_t aTags = countTags(a.c);
      size_t bTags = countTags(b.c);
      return aTags > bTags; 
    }
      
    return a.c.size() < b.c.size();
  }
};



#endif
