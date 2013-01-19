CC=g++
LD=g++
INC_PATH=../include/
LIB_PATH=../lib/
CFLAGS=-c -O3 -Wall -I$(INC_PATH) `pkg-config --cflags glibmm-2.4`
LDFLAGS=`pkg-config --libs glibmm-2.4`

all: lemmatizer

lemmatizer: main.o corpora_io.o dict.o
	$(LD) -O3 main.o corpora_io.o dict.o $(LDFLAGS) -o lemmatizer

main.o: main.cpp $(INC_PATH)tag.h $(INC_PATH)token.h $(INC_PATH)utils.h $(INC_PATH)sentence.h $(INC_PATH)corpora_io.h $(INC_PATH)dict.h
	$(CC) $(CFLAGS) main.cpp

corpora_io.o: $(LIB_PATH)corpora_io.cpp $(INC_PATH)corpora_io.h $(INC_PATH)sentence.h $(INC_PATH)token.h $(INC_PATH)tag.h $(INC_PATH)utils.h 
	$(CC) $(CFLAGS) $(LIB_PATH)corpora_io.cpp

dict.o: $(LIB_PATH)dict.cpp $(INC_PATH)dict.h
	$(CC) $(CFLAGS) $(LIB_PATH)dict.cpp
