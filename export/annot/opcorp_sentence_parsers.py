#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import opcorp_basic_parsers
from xml.sax.saxutils import escape

"""
methods for removing sentences containing ambiguous tokens
"""
class OpcorpSentenceRemover(opcorp_basic_parsers.OpcorpBasicParser):
    

    def __init__(self, out_filepath, encoding):
        super().__init__(set())

        self.file = None
        
        #save input parameters
        self.out_filepath = out_filepath
        self.encoding = encoding    
    
        self.is_sentence_started = False  
        self.is_source_started = False
        self.num_of_sentences = 0
        
        self._new_file()  


    #save the sentence data
    #in order to write it later if the sentence has been found to be unambiguous
    def startElement(self, name, attrs):
        #we'll write the paragraph later
        #if it has at least one unambiguous sentence

        
        if name == self.TAG_PARAGRAPH:
            self.num_of_sentences = 0
            self.paragraph_attrs = attrs
        
        elif name == self.TAG_SENTENCE:

            
            self.num_of_sentences += 1
            self.num_of_tokens = 0
            self.is_sentence_started = True
            self.is_ambiguous = False
            self.token_attrs = []
            self.tfr_attrs = []
            self.lexeme_attrs = []
            self.grammeme_attrs = []
            self.sentence_attrs = attrs
            
        elif name == self.TAG_SOURCE:
            self.is_source_started = True
            
        elif name == self.TAG_TOKEN:

            
            self.num_of_variants = 0
            if not self.is_ambiguous:
                self.token_attrs.append(attrs)
                self.num_of_tokens += 1
                
        elif (name == self.TAG_TFR) and (not self.is_ambiguous):
                self.tfr_attrs.append(attrs)

        elif (name == self.TAG_VARIANT) and (not self.is_ambiguous):
            self.num_of_variants += 1
            if self.num_of_variants > 1:
                self.num_of_sentences -= 1
                self.is_ambiguous = True
            else:
                self.is_first_grammeme = True
            
        elif (name == self.TAG_LEXEME) and (not self.is_ambiguous):
                self.lexeme_attrs.append(attrs)
        
        elif (name == self.TAG_GRAMMEME) and (not self.is_ambiguous):
                if self.is_first_grammeme:
                    self.grammeme_attrs.append([])
                    self.is_first_grammeme = False
                
                self.grammeme_attrs[-1].append(attrs)

        elif (not self.is_sentence_started):
            self.file.write(self._gen_start_tag(name, attrs))
            

    def endElement(self, name):
        if name == self.TAG_SENTENCE:
            self.is_sentence_started = False
            if not self.is_ambiguous:
                self._write_current_sentence()
                
        elif name == self.TAG_SOURCE:
            self.is_source_started = False
        
        elif (not self.is_sentence_started) and (name != self.TAG_PARAGRAPH or
                                                self.num_of_sentences > 0):
            self.file.write(self._gen_end_tag(name))
            
    def characters(self, content):
        #we'll write the <source> tag only if the sentence tag has been written
        if self.is_source_started:
            self.sentence_source = content.strip()
        else:
            self.file.write(escape(content.strip()).encode(self.encoding))
            
    def _write_current_sentence(self):
        #write the paragraph tag for the first sentence
        if self.num_of_sentences == 1:
            self.file.write(self._gen_start_tag(self.TAG_PARAGRAPH, self.paragraph_attrs))
        self.file.write(self._gen_start_tag(self.TAG_SENTENCE, self.sentence_attrs))
        self.file.write(self._gen_start_tag(self.TAG_SOURCE, None))
        self.file.write(escape(self.sentence_source).encode(self.encoding))
        self.file.write(self._gen_end_tag(self.TAG_SOURCE))
        
        self.file.write(self._gen_start_tag(self.TAG_TOKENS, None))

        
        for token_index in range(0, self.num_of_tokens):
            
            
            self.file.write(self._gen_start_tag(self.TAG_TOKEN, self.token_attrs[token_index]))
            self.file.write(self._gen_start_tag(self.TAG_TFR, self.tfr_attrs[token_index]))
            self.file.write(self._gen_start_tag(self.TAG_VARIANT, None))
            self.file.write(self._gen_start_tag(self.TAG_LEXEME, self.lexeme_attrs[token_index]))  
            
            for grammeme_attrs in self.grammeme_attrs[token_index]:
                self.file.write(self._gen_start_tag(self.TAG_GRAMMEME, grammeme_attrs))  
                self.file.write(self._gen_end_tag(self.TAG_GRAMMEME))
                
            self.file.write(self._gen_end_tag(self.TAG_LEXEME))
            self.file.write(self._gen_end_tag(self.TAG_VARIANT))
            self.file.write(self._gen_end_tag(self.TAG_TFR))
            self.file.write(self._gen_end_tag(self.TAG_TOKEN))
        
        self.file.write(self._gen_end_tag(self.TAG_TOKENS))
        
        self.file.write(self._gen_end_tag(self.TAG_SENTENCE))
        
        