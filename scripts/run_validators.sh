#!/bin/bash
config=/corpus/config.ini

python /corpus/scripts/validators/year_valid.py $config
python /corpus/scripts/validators/author_validator.py $config
python /corpus/scripts/validators/url_validator.py $config
python /corpus/scripts/validators/par_validator.py $config
/corpus/scripts/find_good_sentences.py $config
