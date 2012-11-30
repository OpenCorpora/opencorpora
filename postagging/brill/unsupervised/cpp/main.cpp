#include <iostream>
#include <fstream>
#include <sstream>
#include <vector>
#include <list>
#include <set>
#include <map>
#include <string>

#include "tag.h"
#include "token.h"
#include "sentence.h"
#include "corpora_io.h"

using namespace std;

string toString(const map<TagSet, size_t> &m);
string toString(const map<string, size_t> &m);

struct TagStat {
  size_t freq;
  map<TagSet, size_t> leftTag;
  map<TagSet, size_t> rightTag;
  map<string, size_t> leftWord;
  map<string, size_t> rightWord;

  string str() const;
};

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
  cerr << "1" << endl;
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
  SentenceCollection::const_iterator cit = sc.begin();
  while (sc.end() != cit) {
    for (size_t i = 1; i < cit->size()-1; i++) {
      TagSet POST = cit->getToken(i).getPOST();
      tStat[POST].freq += 1;
      TagStat& r = tStat[POST];
      r.leftTag[cit->getToken(i-1).getPOST()] += 1;
      r.rightTag[cit->getToken(i+1).getPOST()] += 1;
      r.leftWord[cit->getToken(i-1).getText()] += 1;
      r.rightWord[cit->getToken(i+1).getText()] += 1;
    }
    
    cit++;
  }
  return;
  map<TagSet, TagStat>::const_iterator mcit = tStat.begin();
  while (tStat.end() != mcit) {
    cout << mcit->first.str() << '\t' << mcit->second.str() << endl;
    mcit++;
  } 
}

string toString(const map<TagSet, size_t> &m) {
  map<TagSet, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first.str() << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

string toString(const map<string, size_t> &m) {
  map<string, size_t>::const_iterator cit = m.begin();
  stringstream ss;
  while (m.end() != cit) {
    ss << '\t' << cit->first << '\t' << cit->second << endl;
    cit++;
  }

  return ss.str();
}

string TagStat::str() const {
  stringstream ss;
  ss << "freq = " << freq << endl;
  ss << "leftTag:" << endl << toString(leftTag) << endl;
  ss << "rightTag:" << endl << toString(rightTag) << endl; 
  ss << "leftWord:" << endl << toString(leftWord) << endl; 
  ss << "rightWord:" << endl << toString(rightWord) << endl;
  ss << "-----------------" << endl; 

  return ss.str();
}

