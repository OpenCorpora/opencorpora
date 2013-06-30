CC=g++
INC_PATH=../include/
LIB_PATH=../lib/
CFLAGS=-c -O3 -std=c++0x -Wall -I$(INC_PATH)
SOURCES=main.cpp aux.cpp $(LIB_PATH)corpora_io.cpp $(LIB_PATH)corpus_stat.cpp $(LIB_PATH)brill.cpp
OBJECTS=$(SOURCES:.cpp=.o)
INCLUDES=$(wildcard $(INC_PATH)/*.h) $(wildcard *.h)

all: train

train: $(OBJECTS)
	$(CC) -O3 $(OBJECTS) -o train

.cpp.o: $< ${INCLUDES}
	$(CC) $(CFLAGS) $< -o $@

clean:
	rm -rf *.o train
