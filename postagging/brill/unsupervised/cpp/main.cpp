#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <list>
#include <set>
#include <map>
#include <string>

using namespace std;

std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems);

struct Tag {
  int v;
public:
  Tag(const string &str) {
    if (4 != str.size()) {
      cerr << "Bad grammeme: \"" << str << "\" of size " << str.size() << endl;
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

  inline string str() const {
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
  TagSet(const string &str) {
    vector<string> v;
    split(str, ' ', v);
    for (int i = 0; i < v.size(); i++) {
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
    set<Tag>::const_iterator cit = s.begin();
    while (s.end() != cit) {
      if (cit->isPOST())
        POSTag = *cit;
      cit++;
    }

    if ("ERRR" == POSTag.str()) throw;
    return POSTag;
  }


  string str() const {
    set<Tag>::const_iterator cit = s.begin();
    string r; int i = 0;
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

class Token {
  string text;
  set<TagSet> var;

public:
  Token(const string &str, const set<TagSet> &v) : text(str), var(v) { }

  const string getText() const {
    return text;
  }

  TagSet getPOST() const {
    TagSet POSTagSet("");
    set<TagSet>::const_iterator cit = var.begin();
    while (var.end() != cit) {
      POSTagSet.insert(cit->getPOST());
      cit++;
    }
    return POSTagSet;
  }

  string str() const {
    stringstream ss;
    ss << text << '\t';
    set<TagSet>::const_iterator cit = var.begin();
    int i = 0;
    while (var.end() != cit) {
      ss << cit->str();
      if (i < var.size()) ss << '\t';
      cit++;
    }
    return ss.str();
  }
};

class Sentence {
  vector<Token> v;
  vector<int> vId;
  map<int, size_t> id2pos;

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

  const Token& getToken(size_t pos) const {
    return v[pos];
  }

  string str() const {
    stringstream ss;
    for (int i = 0; i < v.size(); i++) 
      ss << vId[i] << '\t' << v[i].str() << endl;

    return ss.str();
  }
};

inline string toString(const map<TagSet, size_t> &m) {
  map<TagSet, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first.str() << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

inline string toString(const map<string, size_t> &m) {
  map<string, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

struct TagStat {
  size_t freq;
  map<TagSet, size_t> leftTag;
  map<TagSet, size_t> rightTag;
  map<string, size_t> leftWord;
  map<string, size_t> rightWord;

  string str() const {
    stringstream ss;
    ss << "freq = " << freq << endl;
    ss << "leftTag:" << endl << toString(leftTag) << endl;
    ss << "rightTag:" << endl << toString(rightTag) << endl; 
    ss << "leftWord:" << endl << toString(leftWord) << endl; 
    ss << "rightWord:" << endl << toString(rightWord) << endl;
    ss << "-----------------" << endl; 
    return ss.str();
  }
};

typedef std::list<Sentence> SentenceCollection;

void readCorpus(const string &fn, SentenceCollection &sc);
void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat);
void DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat); 

SentenceCollection originalCorpus;
SentenceCollection currentCorpus;

map<TagSet, TagStat> tagStat;

int main(int argc, char **argv) {
  if (argc > 1)
    readCorpus(argv[1], originalCorpus);
  else {
    cerr << "corpus file is missing" << endl;
    return -1;
  }

  currentCorpus = originalCorpus;

  // TODO: делать это в цикле до тех пор, пока годных правил не останется
  DoOneStep(currentCorpus, tagStat);

  return 0;

  cout << currentCorpus.begin()->str() << endl;

  SentenceCollection::const_iterator cit = currentCorpus.begin();
  while (currentCorpus.end() != cit) {
    for (size_t i = 0; i < cit->size(); i++) {
      const Token &t = cit->getToken(i);
      cout << "//" << i << '\t' << t.str() << endl;
      cout << i << '\t' << t.getText() << '\t' << t.getPOST().str() << endl; 
    }
    cit++;
  }

  return 0;
}

void DoOneStep(SentenceCollection &sc, map<TagSet, TagStat> &tStat) {
  tStat.clear();
  UpdateCorpusStatistics(sc, tStat);
 
  // TODO: сделать тип struct Rule
  // TODO: сгенерировать список правил и выбрать лучшее
  // Rule bestRule; 
  // FindBestRule(tStat, bestRule); // прототип функции void FindBestRule(const map<TagSet, TagStat> &tStat, Rule &rule);
 
  // TODO: применить это лучшее правило к корпусу
  // ApplyRule(sc, bestRule); // void ApplyRule(SentenceCollection &sc, const Rule &rule);
  
  // TODO: сложить правило в какой-нибудь list<Rule>
 
}

void UpdateCorpusStatistics(const SentenceCollection &sc, map<TagSet, TagStat> &tStat) {

}


std::vector<std::string> &split(const std::string &s, char delim, std::vector<std::string> &elems) {
    std::stringstream ss(s);
    std::string item;
    while(std::getline(ss, item, delim)) {
        elems.push_back(item);
    }
    return elems;
}

set<TagSet> makeVariants(const string &s) {
  set<TagSet> r;
  TagSet ts(s);
  r.insert(ts);
  return r;
}

void readCorpus(const string &fn, SentenceCollection &sc) {
  ifstream f(fn.c_str());
  if (!f.is_open()) {
    cerr << "can't open \"" << fn << endl;
    throw;
  }

  string s;
  Sentence sent;
  while (getline(f, s)) {
    //cerr << "reading line \"" << s << "\"" << endl;
    if ("<sent>" == s) {
      Token t("SentBegin", makeVariants("SBEG"));
      sent.push_back(t);
    } else if ("</sent>" == s) {
      Token t("SentEnd", makeVariants("SEND"));
      sent.push_back(t);
      sc.push_back(sent);
      sent.clear();
    } else {
      vector<string> fields;
      split(s, '\t', fields);
      int id;
      string word;
      stringstream ss(s);
      ss >> id >> word;

      set<TagSet> variants;
      for (int i = 2; i < fields.size(); i++) {
        if (0 == fields[i].size())
          continue;
        TagSet ts(fields[i]); 
        if (0 == ts.size()) {
          cerr << "\"" << s << "\" - \"" << fields[i] << "\"" << fields[i].size() << endl;
          throw;
        }
        variants.insert(ts);
      }

      Token t(word, variants);
      sent.push_back(t, id);
    }
  }
}
