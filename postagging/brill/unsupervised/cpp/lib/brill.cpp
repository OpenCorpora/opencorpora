
#include <string>
#include <sstream>
#include <cstring>

#include "brill.h"

using namespace std;

void Context::parse(const std::string &s) {
  vector<string> v;
  split(s, '&', v);

  for (vector<string>::iterator cit = v.begin(); v.end() != cit; cit++) {
    //if ('&' == (*cit)[0]) (*cit) = cit->substr(1);
    //cerr << *cit << endl;
    Condition c(*cit);
    elements.insert(c);
  }
}


void Condition::parse(const string &s) {
  stringstream ss(s);
  char c;

  while (ss.good() && ' ' == ss.peek()) ss.get(c);
  
  // Consume position
  signed int p;
  ss >> p;

  // Consume ':'
  ss >> c;
  if (':' != c) throw;

  // Consume condition type
  char buff[8];
  EType w;
  if ('t' == ss.peek()) {
    ss.get(buff, 4);
    w = tag;
  } else if ('w' == ss.peek()) {
    ss.get(buff, 5);
    w = word;
  } else throw;

  // Consume '='
  ss >> c;
  if ('=' != c) throw;

  if (tag == w) {
    // Consume tag set
    TagSet ts;
    while (!ss.eof()) {
      do { ss.get(c); } while (!ss.eof() && ' ' == c);
      if ('#' == c) break;
      ss.unget();
      ss.get(buff, 5);
      if (0 == strlen(buff)) break;
      //buff[4] = 0;
      Tag t(buff);
      ts.insert(t);
    }  

    value = ts; 
  } else {
    stringbuf sb;
    ss.get(sb, ' ');

    form = sb.str();
  }

  // Если мы тут, то всё прочиталось хорошо
  pos = p;
  what = w;
}
