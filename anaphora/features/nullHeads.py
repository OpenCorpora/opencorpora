# -*- coding: utf-8 -*-

import sys
sys.path.append('/corpus/python')
from Annotation import AnnotationEditor
editor = AnnotationEditor('config.ini')


editor.db_cursor.execute("SHOW columns FROM syntax_groups")
has_marks = False
rows = editor.db_cursor.fetchall()


# добавлять колонку "marks" только если её не существует
for row in rows:
    if row['Field'] == 'marks':
        has_marks = True
        break

if not has_marks:
    editor.db_cursor.execute("ALTER TABLE syntax_groups ADD marks ENUM('bad', 'suspicious', 'no head', 'all')")


""" ==============================
        ПРОСТЫЕ ГРУППЫ
    ============================== """


# убрать вершины, добавить тэг "нет вершины" в вводных выражениях, сложных союзах, сложных предлогах, наречных выражениях
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, group_type FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id WHERE group_type IN (4, 5, 6, 7)")

expressions = editor.db_cursor.fetchall()

for row in expressions:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = 0, marks = 'no head' WHERE group_id = " + str(row['group_id']))       

editor.commit()



# в базовых группах с одним существительным поставить существительное вершиной 
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, token_id, syntax_groups.group_type, syntax_groups.head_id, tf_revisions.tf_id, COUNT(tf_revisions.tf_id) AS tf_count FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id INNER JOIN tf_revisions ON syntax_groups_simple.token_id = tf_revisions.tf_id WHERE rev_text LIKE '%NOUN%' AND head_id = 0 AND group_type = 1 AND tf_revisions.is_last = 1 GROUP BY group_id HAVING tf_count = 1")

single_noun_base = editor.db_cursor.fetchall()

for row in single_noun_base:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['tf_id']) + " WHERE group_id = " + str(row['group_id']))

editor.commit()


# в базовых группах из 1 токена поставить вершиной номер составляющего токена (если слово - не сущ./прил. - тэг "подозрительно") 
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, token_id, rev_text LIKE \"%<g v='NOUN'%\" AS is_noun, rev_text LIKE \"%<g v='ADJF'%\" AS is_adjective, COUNT(token_id) AS tk FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id INNER JOIN tf_revisions ON syntax_groups_simple.token_id = tf_revisions.tf_id WHERE head_id = 0 AND group_type = 1 AND tf_revisions.is_last = 1 GROUP BY group_id HAVING tk = 1")

single_token_base = editor.db_cursor.fetchall()

for row in single_token_base:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['token_id']) + " WHERE group_id = " + str(row['group_id']))
    if row['is_noun'] == 0 and row['is_adjective'] == 0:
        editor.db_cursor.execute("UPDATE syntax_groups SET marks = \'suspicious\' WHERE group_id = " + str(row['group_id']))

editor.commit()


# в именах собственных, содержащих только одно существительное, поставить это существительное вершиной
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id,tf_revisions.tf_id, COUNT(tf_revisions.tf_id) AS tf_count FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id INNER JOIN tf_revisions ON syntax_groups_simple.token_id = tf_revisions.tf_id WHERE rev_text LIKE '%NOUN%' AND head_id = 0 AND group_type = 2 AND tf_revisions.is_last = 1 GROUP BY group_id HAVING tf_count = 1")

single_noun_personal = editor.db_cursor.fetchall()

for row in single_noun_personal:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['tf_id']) + " WHERE group_id = " + str(row['group_id']))

editor.commit()


# в именах собственных, состоящих из 1 токена (не сущ.), поставить вершиной номер токена + тэг "подозрительно"
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, token_id, COUNT(token_id) AS tk FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id WHERE head_id = 0 AND group_type = 2 GROUP BY group_id HAVING tk = 1")

single_token_personal = editor.db_cursor.fetchall()

for row in single_token_personal:
    editor.db_cursor.execute("UPDATE syntax_groups SET marks = 'suspicious', head_id = " + str(row['token_id']) + " WHERE group_id = " + str(row['group_id']))

editor.commit()


# в количественных группах, содержащих только одно числительное, поставить числительное вершиной
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, tf_revisions.tf_id, COUNT(tf_revisions.tf_id) AS tf_count FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id INNER JOIN tf_revisions ON syntax_groups_simple.token_id = tf_revisions.tf_id WHERE rev_text LIKE '%NUMR%' AND head_id = 0 AND group_type = 3 AND tf_revisions.is_last = 1 GROUP BY group_id HAVING tf_count = 1")

single_numeral = editor.db_cursor.fetchall()

for row in single_numeral:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['tf_id']) + " WHERE group_id = " + str(row['group_id']))

editor.commit()


# в количественных группах, где больше 1го числительного, вершина - последнее числительное
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id, tf_revisions.tf_id, COUNT(tf_revisions.tf_id) AS tf_count FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id INNER JOIN tf_revisions ON syntax_groups_simple.token_id = tf_revisions.tf_id WHERE rev_text LIKE '%NUMR%' AND head_id = 0 AND group_type = 3 AND tf_revisions.is_last = 1 GROUP BY group_id HAVING tf_count > 1")

few_numerals = editor.db_cursor.fetchall()

for row in few_numerals:
    editor.db_cursor.execute("SELECT MAX(tf_id) as max_tf_id FROM few_numerals WHERE group_id = " + row['group_id'])
    last_numeral = editor.db_cursor.fetchone()
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(last_numeral['max_tf_id']) + " WHERE group_id = " + str(row['group_id']))

editor.commit()


# все остальные базовые группы, имена собственные и количественные группы - плохие группы
editor.db_cursor.execute("SELECT syntax_groups_simple.group_id FROM syntax_groups_simple INNER JOIN syntax_groups ON syntax_groups_simple.group_id = syntax_groups.group_id WHERE head_id = 0 AND group_type IN (1, 2, 3)")

bad_base_groups = editor.db_cursor.fetchall()
for row in bad_base_groups:
    editor.db_cursor.execute("UPDATE syntax_groups SET marks = 'bad' WHERE group_id = " + str(row['group_id']))

editor.commit()


""" ===============================
           СЛОЖНЫЕ ГРУППЫ
    ==============================="""


# в предложных группах, где есть только один предлог и нет сложных предлогов, поставить вершшиной предлог
editor.db_cursor.execute("SELECT complex.parent_gid, complex.child_gid, syntax_groups_simple.token_id, COUNT(token_id) as tk FROM syntax_groups AS comp_g INNER JOIN syntax_groups_complex AS complex ON complex.parent_gid = comp_g.group_id INNER JOIN syntax_groups AS simp_g ON simp_g.group_id = complex.child_gid INNER JOIN syntax_groups_simple ON syntax_groups_simple.group_id = complex.child_gid INNER JOIN tf_revisions ON tf_revisions.tf_id = syntax_groups_simple.token_id WHERE tf_revisions.is_last = 1 AND comp_g.head_id = 0 AND comp_g.group_type = 11 AND tf_revisions.rev_text LIKE '%PREP%' AND simp_g.group_type != 4 GROUP BY parent_gid HAVING tk = 1")

single_prep = editor.db_cursor.fetchall()

for row in single_prep:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['token_id']) + " WHERE group_id = " + str(row['parent_gid']))

editor.commit()


# в сложных предложных группах, где есть только один сложный предлог и нет предлогов, поставить вершиной номер группы "сложный предлог"
editor.db_cursor.execute("SELECT complex.parent_gid, complex.child_gid, syntax_groups_simple.token_id, COUNT(child_gid) as children FROM syntax_groups AS comp_g INNER JOIN syntax_groups_complex AS complex ON complex.parent_gid = comp_g.group_id INNER JOIN syntax_groups AS simp_g ON simp_g.group_id = complex.child_gid INNER JOIN syntax_groups_simple ON syntax_groups_simple.group_id = complex.child_gid INNER JOIN tf_revisions ON tf_revisions.tf_id = syntax_groups_simple.token_id WHERE tf_revisions.is_last = 1 AND comp_g.head_id = 0 AND comp_g.group_type = 11 AND tf_revisions.rev_text NOT LIKE '%PREP%' AND simp_g.group_type = 4 GROUP BY parent_gid HAVING children = 1")

single_comp_prep = editor.db_cursor.fetchall()

for row in single_comp_prep:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['child_gid']) + " WHERE group_id = " + str(row['parent_gid']))

editor.commit()


# в группах типа "перечисление" убрать вершины, поставить тэг "all"
editor.db_cursor.execute("SELECT syntax_groups_complex.parent_gid FROM syntax_groups_complex INNER JOIN syntax_groups ON syntax_groups_complex.parent_gid = syntax_groups.group_id WHERE group_type = 12")

lists = editor.db_cursor.fetchall()

for row in lists:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = 0, marks = 'all' WHERE group_id = " + str(row['parent_gid']))       

editor.commit()


# в сложном собственном наименовании поставить вершиной имя
editor.db_cursor.execute("SELECT complex.parent_gid, complex.child_gid, syntax_groups_simple.token_id, COUNT(token_id) as tk FROM syntax_groups AS comp_g INNER JOIN syntax_groups_complex AS complex ON complex.parent_gid = comp_g.group_id INNER JOIN syntax_groups AS simp_g ON simp_g.group_id = complex.child_gid INNER JOIN syntax_groups_simple ON syntax_groups_simple.group_id = complex.child_gid INNER JOIN tf_revisions ON tf_revisions.tf_id = syntax_groups_simple.token_id WHERE tf_revisions.is_last = 1 AND comp_g.head_id = 0 AND comp_g.group_type = 8 AND tf_revisions.rev_text LIKE '%Name%' GROUP BY parent_gid HAVING tk = 1")

name_in_name = editor.db_cursor.fetchall()

for row in name_in_name:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['token_id']) + " WHERE group_id = " + str(row['parent_gid']))

editor.commit()


# в сложном несобственном наименовании, где есть только одна базовая группа, поставить вершиной базовую группу
editor.db_cursor.execute("SELECT complex.parent_gid, complex.child_gid, syntax_groups_simple.token_id, COUNT(child_gid) as children FROM syntax_groups AS comp_g INNER JOIN syntax_groups_complex AS complex ON complex.parent_gid = comp_g.group_id INNER JOIN syntax_groups AS simp_g ON simp_g.group_id = complex.child_gid INNER JOIN syntax_groups_simple ON syntax_groups_simple.group_id = complex.child_gid INNER JOIN tf_revisions ON tf_revisions.tf_id = syntax_groups_simple.token_id WHERE tf_revisions.is_last = 1 AND comp_g.head_id = 0 AND comp_g.group_type = 9 AND simp_g.group_type = 1 GROUP BY parent_gid HAVING children = 1")

base_in_name = editor.db_cursor.fetchall()

for row in base_in_name:
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(row['child_gid']) + " WHERE group_id = " + str(row['parent_gid']))

editor.commit()


# в приложениях, где ровно 2 базовые группы, поставить вершиной первую базовую группу
editor.db_cursor.execute("SELECT complex.parent_gid, complex.child_gid, COUNT(child_gid) as children FROM syntax_groups AS comp_g INNER JOIN syntax_groups_complex AS complex ON complex.parent_gid = comp_g.group_id INNER JOIN syntax_groups AS simp_g ON simp_g.group_id = complex.child_gid INNER JOIN syntax_groups_simple ON syntax_groups_simple.group_id = complex.child_gid WHERE comp_g.head_id = 0 AND comp_g.group_type = 10 AND simp_g.group_type = 1 GROUP BY parent_gid HAVING children = 2")

supplement = editor.db_cursor.fetchall()

for row in supplement:
    editor.db_cursor.execute("SELECT MIN (child_gid) as first_group FROM supplement WHERE parent_gid = " + row['parent_gid'])
    first_base = editor.db_cursor.fetchone()
    editor.db_cursor.execute("UPDATE syntax_groups SET head_id = " + str(first_base['first_group']) + " WHERE group_id = " + str(row['parent_gid']))

editor.commit()


# все оставшиеся сложные группы: предложные, собственные наименования, несобственные наименования, приложения, именные - плохие группы
editor.db_cursor.execute("SELECT syntax_groups_complex.parent_gid, syntax_groups.group_type FROM syntax_groups_complex INNER JOIN syntax_groups ON syntax_groups_complex.parent_gid = syntax_groups.group_id WHERE head_id = 0 AND group_type IN (8, 9, 10, 11, 13)")

bad_complex_groups = editor.db_cursor.fetchall()
for row in bad_complex_groups:
    editor.db_cursor.execute("UPDATE syntax_groups SET marks = 'bad' WHERE group_id = " + str(row['parent_gid']))

editor.commit()

