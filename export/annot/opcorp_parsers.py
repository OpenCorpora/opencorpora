#!/usr/bin/env python2
# -*- coding: utf-8 -*-

import re
from xml.sax.xmlreader import AttributesImpl


import no_homonymy_constants
import opcorp_basic_parsers

class OpcorpTokenVariantRemover(opcorp_basic_parsers.OpcorpBasicParser):
    def __init__(self, out_filepath, tokensWithAgreement, encoding):
        super().__init__(tokensWithAgreement.keys())

        self.file = None
        
        #save input parameters
        self.out_filepath = out_filepath
        self.tokensWithAgreement = tokensWithAgreement
        self.encoding = encoding        

        #save the id of the token we've found
        self.current_token = None
        
        # the flag showing if we've written the starting tag
        #if we've skipped the starting tag, we'll also skip the ending tag
        self.is_start_tag_written = False
        
        self.is_start_variant_tag_written = False
        
        #the flag showing if the <g> tag is the first for the lexeme
        self.is_first_grammeme = False

        #collect the max set of grammemes for each variant
        self.tokens_max_variant_arrays = {}
        
        self._new_file()
        
        
        self.is_doubtful_variant = False
    
    #generates the <g> tag and the <v><l> structure if necessary
    def _write_grammeme_tag(self, name, attrs, is_to_generate_wrapper):
        if is_to_generate_wrapper:
            self.is_start_variant_tag_written = True
            self.file.write(self._gen_start_tag('v', None))
            self.file.write(self._gen_start_tag('l', self.lexeme_attrs))
        self.file.write(self._gen_start_tag(name, attrs))
            
            
    def _add_to_current_grammeme_set(self, grammeme, isFirstGrammeme):
        if isFirstGrammeme:
            self.current_set = set()
        self.current_set.add(grammeme)
        

    def startElement(self, name, attrs):
        self.is_start_tag_written = True
        
                
        if name == 'token' \
                and not self._areAllTokensFound():
            
            self.file.write(self._gen_start_tag(name, attrs))
            fid = attrs.get('id')


            if self.tokensWithAgreement.get(fid):
                self.current_token = fid
                self.ids_left -= 1
            
        #we skip the <v> or <l> tags because we may write them later
        elif self.current_token and name == 'v':
            self.is_doubtful_variant = True
            self.is_first_grammeme = True
            self.is_start_variant_tag_written = False
            self.current_set = None
        
        elif self.current_token and name == 'l':
            self.is_doubtful_variant = True
            self.lexeme_attrs = attrs
            self.is_start_variant_tag_written = False
                
        elif self.current_token and (name == 'g'):
            grammemeValue = attrs.get('v')
            poolTypes = self.tokensWithAgreement[self.current_token]
            
            for poolType, decision in poolTypes.items():
                if poolType is None:
                    continue
                poolVariants = set(re.split('\\s*[&@]\\s*', poolType))
                decisions = set(re.split('\\s*&\\s*', decision))
                
                #we write UNKNOWN grammemes instead of all grammemes
                if (no_homonymy_constants.DECISION_UNKNOWN in decisions):
                    self._add_to_current_grammeme_set(no_homonymy_constants.DECISION_UNKNOWN, self.is_first_grammeme)
                    
                    newAttrs = AttributesImpl({'v':no_homonymy_constants.DECISION_UNKNOWN})
                    newLexemeAttrs = AttributesImpl({'t':self.lexeme_attrs.get('t'), 'id':'0'})
                    self.lexeme_attrs = newLexemeAttrs
                    
                    self._write_grammeme_tag(name, newAttrs, self.is_first_grammeme)
                    self.is_first_grammeme = False
                #the grammeme should be written in two cases:
                #-there are no doubts about it
                #-it has been chosen by the annotators  
                elif (grammemeValue not in poolVariants) or (grammemeValue in decisions):
                    self._add_to_current_grammeme_set(grammemeValue, self.is_first_grammeme)
                    
                    self._write_grammeme_tag(name, attrs, self.is_first_grammeme)
                    self.is_first_grammeme = False
                #the gramme
                else:
                    self.is_start_tag_written = False
                    
            
        else:
            self.file.write(self._gen_start_tag(name, attrs))
            

    def endElement(self, name): 
  
        if not name in ['v', 'l', 'g'] \
            or (name == 'g' and self.is_start_tag_written) \
            or (name in ['v', 'l'] and (not self.is_doubtful_variant or self.is_start_variant_tag_written)):
            
            self.file.write(self._gen_end_tag(name))
    
            
            if name == 'v' and self.is_start_variant_tag_written:
                
                
                if self.current_token == u"1284320":
                    aaa = 9
                
                if not self.current_token in self.tokens_max_variant_arrays:
                    self.tokens_max_variant_arrays[self.current_token] = dict()
 
                
                currentGrammemeSet = frozenset(self.current_set.copy())

                currentLexemeAttrs = tuple(self.lexeme_attrs.items())
                
                isSubset = False
                otherSubsetsToRemove = []
                for anotherSet, anotherLexemeAttrs in self.tokens_max_variant_arrays[self.current_token].items():
                    if currentGrammemeSet.issubset(anotherSet):
                        isSubset = True
                        break
                    if currentGrammemeSet.issuperset(anotherSet):
                        otherSubsetsToRemove.append(anotherSet)
                        
                if not isSubset:
                    self.tokens_max_variant_arrays[self.current_token][currentGrammemeSet] = set()
                    self.tokens_max_variant_arrays[self.current_token][currentGrammemeSet].add(currentLexemeAttrs)
                    
                elif currentGrammemeSet in self.tokens_max_variant_arrays[self.current_token]:
                    self.tokens_max_variant_arrays[self.current_token][currentGrammemeSet].add(currentLexemeAttrs)
                    
                
                for anotherSubsetToRemove in otherSubsetsToRemove:
                    self.tokens_max_variant_arrays[self.current_token].pop(anotherSubsetToRemove, None)
                
            
        if name == 'token' and self.current_token:
            self.current_token = None    
            
        elif name == 'v' and self.is_doubtful_variant:
            self.is_doubtful_variant = False
        
        elif name == 'annotation':
            self._close_file()

class OpcorpTokenNormalizer(opcorp_basic_parsers.OpcorpBasicParser):
    def __init__(self, out_filepath, tokens_max_variant_arrays, encoding):
        super().__init__(tokens_max_variant_arrays.keys())

        self.file = None
        
        #save input parameters
        self.out_filepath = out_filepath
        self.encoding = encoding
        self.tokens_max_variant_arrays = tokens_max_variant_arrays
    
        # the flag showing if we've written the starting tag
        #if we've skipped the starting tag, we'll also skip the ending tag
        self.is_start_tag_written = False


        self.current_token = None
        
        self.is_start_variant_tag_written = False
        
        #the flag showing if the <g> tag is the first for the lexeme
        self.is_first_grammeme = False


        self.current_set = None
        
        self._new_file()
        
        
    def _write_grammeme_set(self, lex_attrs_set, current_grammemes):
        for lexeme_attrs_tuple in lex_attrs_set:
            self.file.write(self._gen_start_tag('v', None))
            
            lexemeDict = {}
            for lexemeAttrItem in lexeme_attrs_tuple:
                lexemeDict[lexemeAttrItem[0]] = lexemeAttrItem[1]
              
            lexeme_attrs = AttributesImpl(lexemeDict)
            
            self.file.write(self._gen_start_tag('l', lexeme_attrs))
            for grammeme in current_grammemes:
                newAttrs = AttributesImpl({'v' : grammeme})
                self.file.write(self._gen_start_tag('g', newAttrs))
                self.file.write(self._gen_end_tag('g'))
                
            self.file.write(self._gen_end_tag('l'))
            self.file.write(self._gen_end_tag('v'))
    
    def startElement(self, name, attrs):
        self.is_start_tag_written = True
        
        if name == 'token' \
                and not self._areAllTokensFound():
            
            self.file.write(self._gen_start_tag(name, attrs))
            fid = attrs.get('id')

            if self.tokens_max_variant_arrays.get(fid):
                self.current_token = fid
                self.ids_left -= 1
                
        #we skip the <v> or <l> tags because we may write them later
        elif self.current_token and name == 'v':
            self.current_grammemes = set()
        
        elif self.current_token and name == 'l':
            self.lexeme_attrs = attrs
                
        elif self.current_token and (name == 'g'):
            grammemeValue = attrs.get('v')
            self.current_grammemes.add(grammemeValue)
                    
            
        else:
            self.file.write(self._gen_start_tag(name, attrs))
            

    def endElement(self, name):   
        if not name in ['v', 'l', 'g'] or not(self.current_token):
            self.file.write(self._gen_end_tag(name))
            
        elif name == 'v':
            allMaxGrammemeSets = self.tokens_max_variant_arrays.get(self.current_token).keys()
            
            if self.current_token == u"1284320":
                aaa = 9
            
            currentGrammemesFrozen = frozenset(self.current_grammemes)
            if currentGrammemesFrozen in allMaxGrammemeSets:
                setOfCurrentLexAttrs = self.tokens_max_variant_arrays.get(self.current_token).get(currentGrammemesFrozen)
                self._write_grammeme_set(setOfCurrentLexAttrs, currentGrammemesFrozen)
                self.tokens_max_variant_arrays.get(self.current_token).pop(currentGrammemesFrozen, None)
                
            
            
        if name == 'annotation':
            self._close_file()
            
        if name == 'token' and self.current_token:
            self.current_token = None
