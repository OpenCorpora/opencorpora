# -*- coding: utf-8 -*-
from datetime import datetime
import string
from collections import defaultdict
import random
import ConfigParser
import MySQLdb
from MySQLdb.cursors import DictCursor


class FeatureCollector(object):
    def __init__(self, left, current, right, abbr_dict):
        self.left_token = left
        self.current_token = current
        self.right_token = right
        self.abbr_dict = abbr_dict
        self.feature_vector = list()
        self.calc_feature_vector()

    def calc_feature_vector(self):

        # нужно добавить информацию о границе абзаца (она будет и при делении на токены?)
        self.feature_vector.append(self.is_first())
        self.feature_vector.append(self.is_last())
        for tok in [self.left_token, self.current_token, self.right_token]:
            self.feature_vector.append(self.is_terminal_punct(tok))
            self.feature_vector.append(self.is_openning_bracket(tok))
            self.feature_vector.append(self.is_closing_bracket(tok))
            self.feature_vector.append(self.is_titul(tok))
            self.feature_vector.append(self.is_all_upper(tok))
            # self.feature_vector.append(self.is_in_dict(tok))
            self.feature_vector.extend(self.get_token_class(tok))

    def get_feature_vector(self):
        return self.feature_vector

    def is_terminal_punct(self, token):
        return 1 if token in ['.', '!', '?'] else 0

    def is_openning_bracket(self, token):
        return 1 if token == '(' else 0

    def is_closing_bracket(self, token):
        return 1 if token == ')' else 0

    # starts with upper case
    def is_titul(self, token):
        return 1 if token and token[0].isupper() else 0

    def is_all_upper(self, token):
        return 1 if token and token.isupper() else 0

    def is_in_dict(self, token):
        return 1 if token in self.abbr_dict else 0

    def get_token_class(self, token):
        # кириллица, латиница, цифры, пунктуация, другое
        class_list = [0] * 5

        cyr_num = 0
        lat_num = 0
        punct_num = 0
        other_num = 0
        digit_num = 0
        # basic punctuation: !"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~
        full_punct_marks = string.punctuation + u'«»–'
        if token:
            for i in token:
                # кириллица, включая всю ту, что используется в языках народов России (надо бы проверить)
                if ((1024 <= ord(i) < 1120) or
                        (1162 <= ord(i) < 1328) or
                        (11744 <= ord(i) < 11775) or
                        (192 <= ord(i) < 383)):
                    cyr_num += 1
                elif i.isdigit():
                    digit_num += 1
                elif i in full_punct_marks:
                    punct_num += 1
                elif 65 <= ord(i) <= 122:
                    lat_num += 1
                else:
                    other_num += 1

            for pos_id, num in enumerate([cyr_num, lat_num, punct_num, digit_num, other_num]):
                # вариант с процентом символов каждого типа в токене
                # class_list[pos_id] = round(float(num) / len(token), 3)
                # вариант с 0-1
                class_list[pos_id] = 1 if num != 0 else 0
        return class_list

    def is_first(self):
        # левого контекста нет, следовательно, это первый токен в абзаце
        return 1 if self.left_token is None else 0

    def is_last(self):
        # правого контекста нет, следовательно, это последний токен в абзаце
        return 1 if self.right_token is None else 0


class LearnModel(object):
    #  для каждого токена получить вектор и сказать его вероятность быть границей предложения
    def __init__(self, config_path):
        config = ConfigParser.ConfigParser()
        config.read(config_path)

        hostname = config.get('mysql', 'host')
        dbname = config.get('mysql', 'dbname')
        username = config.get('mysql', 'user')
        password = config.get('mysql', 'passwd')

        self.db_connect = MySQLdb.connect(hostname, username, password, dbname, use_unicode=True, charset="utf8")
        self.db_cursor = self.db_connect.cursor(DictCursor)
        self.db_cursor.execute('START TRANSACTION')
        self.get_data_from_db()

    def is_sent_border(self, row_id, sent_id):
        # определяет, является ли токен граничным
        if (row_id + 1) == len(self.rows) or self.rows[row_id + 1]['sent_id'] != sent_id:
            return 1
        return 0

    def get_data_from_db(self):
        # вытаскиваем айди текста, предложения, токена и сам текст токена,
        # упорядоченные по тому, как токены следуют в предложениях в тексте
        self.db_cursor.execute("""
                        SELECT book_id, par_id, sent_id, tf_id, tf_text
                        FROM tokens
                        JOIN sentences USING (sent_id)
                        JOIN paragraphs USING (par_id)
                        JOIN books USING (book_id)
                        ORDER BY book_id, paragraphs.pos, sentences.pos, tokens.pos
                    """)
        self.rows = self.db_cursor.fetchall()

    def collect_data(self):
        vectors_dict = defaultdict(list)

        for row_id, row in enumerate(self.rows):
            left, current, right = self.get_context(row_id, row)
            feature_obj = FeatureCollector(left, current, right, {})
            feature_vector = feature_obj.get_feature_vector()
            if tuple(feature_vector) not in vectors_dict:
                vectors_dict[tuple(feature_vector)] = [0] * 2

            # всего таких векторов встретилось в базе
            vectors_dict[tuple(feature_vector)][0] += 1

            # векторов, для которых current token является границей предложения
            if self.is_sent_border(row_id, row['sent_id']):
                vectors_dict[tuple(feature_vector)][1] += 1

        vectors_dict_final = dict()
        for feat_vect, val in vectors_dict.items():
            # отладка
            # print feat_vect, val[1], val[0], float(val[1]) / val[0]
            vectors_dict_final[feat_vect] = float(val[1]) / val[0]

        self.model = vectors_dict_final

    def get_context(self, row_id, row):
        """
        Возвращает контекстные тройки токенов.
        (примеры с границей предложения)
        'формате . В' для предложений внутри абзаца,
        'None « Школа' - для первого предложения в абзаце и тексте,
        'сезоне ? None' - для последнего предложения в абзаце и тексте.
        """
        if row_id == 0 or self.rows[row_id - 1]['par_id'] < row['par_id']:
            left = None
        else:
            left = self.rows[row_id - 1]['tf_text']
        current = row['tf_text']

        if (row_id + 1) == len(self.rows) or self.rows[row_id + 1]['par_id'] > row['par_id']:
            right = None
        else:
            right = self.rows[row_id + 1]['tf_text']
        return left, current, right

    def evaluate(self, folds=10, fold_size=10, threshold=0.8):
        # фэйковая кросс-валидация (оцениваемся на подвыборке тех текстов, на которых обучались)
        test_book_ids = list()
        all_test_book_ids = set()
        for fold in range(folds):
            # hardcoded book id limit
            book_ids = random.sample(range(0, 4000), fold_size)
            test_book_ids.append(book_ids)
            all_test_book_ids.update(set(book_ids))

        # eval_res = [0.0, 0.0] * folds  # list of accuracies and precisions
        eval_stats = defaultdict(list)  # dict for true pos, true neg and overall
        prec_stats = defaultdict(list)

        for row_id, row in enumerate(self.rows):

            if row['book_id'] not in all_test_book_ids:
                continue

            left, current, right = self.get_context(row_id, row)
            feature_obj = FeatureCollector(left, current, right, {})
            feature_vector = feature_obj.get_feature_vector()

            true_pos = 0
            true_neg = 0
            if self.model[tuple(feature_vector)] > threshold and self.is_sent_border(row_id, row['sent_id']):
                true_pos = 1
            if self.model[tuple(feature_vector)] <= threshold and not self.is_sent_border(row_id, row['sent_id']):
                true_neg = 1

            # if (true_pos + true_neg) == 0:
            #     print(row)

            for fold_id, el in enumerate(test_book_ids):
                if row['book_id'] in el:
                    if fold_id not in eval_stats:
                        eval_stats[fold_id] = [0] * 2
                        prec_stats[fold_id] = [0] * 2
                    eval_stats[fold_id][0] += true_pos + true_neg  # true pos
                    prec_stats[fold_id][0] += true_pos
                    eval_stats[fold_id][1] += 1
                    if self.is_sent_border(row_id, row['sent_id']):
                        prec_stats[fold_id][1] += 1  # всего токенов в этом fold'e

        # Debug
        # print test_book_ids
        # print eval_stats
        # print prec_stats

        sum_acc = 0
        sum_prec = 0
        # печатаем результаты каждого фолда
        for fold_id, acc_res in sorted(eval_stats.items()):
            acc = round(float(acc_res[0]) / acc_res[1], 4)
            sum_acc += acc
            prec = round(float(prec_stats[fold_id][0]) / prec_stats[fold_id][1], 4)
            sum_prec += prec
            print fold_id, acc, prec

        # усреднённый результат на 10 фолдов
        # print sum(eval_res) / len(eval_res)
        print 'Overall accuracy', sum_acc / folds
        print 'Overall precision', sum_prec / folds


# Пример запуска с подсчётом времени работы и оценки
# startTime = datetime.now()
#
# model = LearnModel('/home/irinfox/opencorpora/trunk/config.ini')
# model.collect_data()
# model.evaluate()

# работает 47-60 секунд
# print datetime.now() - startTime
