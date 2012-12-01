#include <string>
#include <sstream>

#include <glibmm/ustring.h>

#include "tag.h"

#ifndef __DICT_H
#define __DICT_H

class Dict {

  std::set<MorphInterp> unknown;

public:
  Dict() {
    MorphInterp mi(0, "UNKN");
    unknown.insert(mi); 
  }
  
  void load(const std::string &fn);

  const std::set<MorphInterp>& lookup(const Glib::ustring &str) const {
    return unknown;
  }
};

#endif
