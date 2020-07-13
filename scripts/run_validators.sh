#!/bin/bash
CONFIG_INI=/corpus/config.ini
CONFIG_JSON=/corpus/config.json

python /corpus/scripts/validators/year_valid.py $CONFIG_INI
python /corpus/scripts/validators/author_validator.py $CONFIG_INI
python /corpus/scripts/validators/url_validator.py $CONFIG_INI
python /corpus/scripts/validators/par_validator.py $CONFIG_INI
/corpus/scripts/find_good_sentences.py $CONFIG_JSON
