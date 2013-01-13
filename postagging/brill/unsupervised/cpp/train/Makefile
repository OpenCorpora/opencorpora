CC=g++
INC_PATH=../include/
LIB_PATH=../lib/
CFLAGS=-c -Wall -O3 -I$(INC_PATH)

all: train

train: main.o corpora_io.o
	$(CC) -O3 main.o corpora_io.o -o train

main.o: main.cpp $(INC_PATH)tag.h $(INC_PATH)token.h $(INC_PATH)utils.h $(INC_PATH)sentence.h $(INC_PATH)corpora_io.h
	$(CC) $(CFLAGS) main.cpp

corpora_io.o: $(LIB_PATH)corpora_io.cpp $(INC_PATH)corpora_io.h $(INC_PATH)sentence.h $(INC_PATH)token.h $(INC_PATH)tag.h $(INC_PATH)utils.h 
	$(CC) $(CFLAGS) $(LIB_PATH)corpora_io.cpp
