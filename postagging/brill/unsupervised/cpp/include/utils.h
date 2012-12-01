#include <string>
#include <sstream>
#include <vector>
#include <iostream>

#ifndef __UTILS_H
#define __UTILS_H

inline std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems) {
//    std::cerr << "split(\"" << s << "\", \'" << delim << "\' ...)" << std::endl;
    std::stringstream ss(s);
    std::string item;
    while(std::getline(ss, item, delim)) {
        elems.push_back(item);
    }
    return elems;
}

#endif
