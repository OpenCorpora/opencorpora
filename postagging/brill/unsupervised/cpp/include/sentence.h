#include <string>
#include <sstream>
#include <vector>
#include <map>

#include "token.h"

#ifndef __SENTENCE_H
#define __SENTENCE_H

class Sentence {
  std::vector<Token> v;
  std::vector<int> vId;
  std::map<int, size_t> id2pos;

public:
  Sentence() {
    v.reserve(20);
    vId.reserve(20);
  }

  void clear() {
    v.clear();
    id2pos.clear();
    vId.clear();
  }

  void push_back(const Token &t) {
    v.push_back(t);
    vId.push_back(0);
  }

  void push_back(const Token &t, int id) {
    v.push_back(t);
    id2pos[id] = v.size() - 1;
    vId.push_back(id);
  }

  size_t size() const {
    return v.size();
  }

  inline const Token& getToken(size_t pos) const {
    return v[pos];
  }

  const Token& getToken(size_t pos, int &id) const {
    id = vId[pos];
    return v[pos];
  }

  inline Token& getNonConstToken(size_t pos) {
    return v[pos];
  }

  std::string str() const {
    std::stringstream ss;
    for (size_t i = 0; i < v.size(); i++) 
      ss << vId[i] << '\t' << v[i].str() << std::endl;

    return ss.str();
  }
};

#endif
