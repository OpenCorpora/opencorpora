ls *.tab | xargs -I XXX grep -E -o "^[0-9]+" XXX | sort | uniq -c | sort -r -n | head
