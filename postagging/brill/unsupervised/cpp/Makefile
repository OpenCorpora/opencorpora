CC=g++
CFLAGS=-c -Wall

all: brill

brill: main.o corpora_io.o
	$(CC) main.o corpora_io.o -o brill

main.o: main.cpp tag.h token.h utils.h sentence.h corpora_io.h
	$(CC) $(CFLAGS) main.cpp

corpora_io.o: corpora_io.cpp corpora_io.h sentence.h token.h tag.h utils.h 
	$(CC) $(CFLAGS) corpora_io.cpp
