CC=g++
CFLAGS=-c -Wall

all: brill

brill: main.o
	$(CC) main.o -o brill

main.o: main.cpp
	$(CC) $(CFLAGS) main.cpp
