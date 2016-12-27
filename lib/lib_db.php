<?php

//sql wrappers
function sql_fetch_array($q) {
    return $q->fetch();
}
function sql_fetch_assoc($q) {
    return $q->fetch(PDO::FETCH_ASSOC);
}
function sql_num_rows($q) {
    return $q->rowCount();
}
function sql_quote($string) {
    global $pdo_db;
    return $pdo_db->quote($string);
}
function sql_begin() {
    global $transaction_counter;
    global $nested_transaction_counter;
    global $pdo_db;
    if (!$transaction_counter) {
        $pdo_db->beginTransaction();
        ++$transaction_counter;
    } else {
        ++$nested_transaction_counter;
    }
}
function sql_commit() {
    global $transaction_counter;
    global $nested_transaction_counter;
    global $pdo_db;
    if ($nested_transaction_counter) {
        --$nested_transaction_counter;
    } else {
        $pdo_db->commit();
        --$transaction_counter;
    }
}
function sql_query($q, $debug=1, $override_readonly=0) {
    global $config;
    global $pdo_db;
    global $total_time;
    global $total_queries;
    if (file_exists($config['project']['readonly_flag']) && stripos(trim($q), 'select') > 1 && !$override_readonly)
        throw new Exception("Database in readonly mode");
    $debug = isset($_SESSION['debug_mode']) && $debug;
    $time_start = microtime(true);
    if ($debug) {
        $q = preg_replace('/select /i', 'SELECT SQL_NO_CACHE ', $q, 1);
    }

    try {
        if ($debug)
            printf("<table class='debug' width='100%%'><tr><td valign='top' width='20'>%d<td>SQL: %s</td>", $total_queries, htmlspecialchars($q));
        $res = $pdo_db->query($q);
        $time = microtime(true)-$time_start;
        $total_time += $time;
        $total_queries++;
        if ($debug) {
            printf("<td width='100'>%.4f сек.</td><td width='100'>%.4f сек.</td></tr></table>\n", $time, $total_time);
        }
    }
    catch (PDOException $e) {
        if ($debug) {
            printf("<td width='100'>%.4f сек.</td><td width='100'>%.4f сек.</td></tr></table>\n", $time, $total_time);
            print "<table class='debug_error' width='100%'><tr><td colspan='3'>".htmlspecialchars($e->getMessage())."</td></tr></table>\n";
        }
        throw new Exception("DB Error");
    }
    return $res;
}
function sql_fetchall($res) {
    return $res->fetchAll();
}
function sql_insert_id() {
    // MUST BE CALLED BEFORE TRANSACTION COMMIT!
    // otherwise returns 0
    global $pdo_db;
    return $pdo_db->lastInsertId();
}
function sql_prepare($q, $override_readonly=0) {
    global $config;
    global $pdo_db;
    global $total_time;
    $debug = isset($_SESSION['debug_mode']);
    if (file_exists($config['project']['readonly_flag']) && stripos(trim($q), 'select') > 1 && !$override_readonly)
        throw new Exception("Database in readonly mode");
    $time_start = microtime(true);
    if ($debug)
        printf("<table class='debug' width='100%%'><tr><td valign='top' width='20'>*</td><td colspan='3'>PREPARE: %s</td></tr></table>\n", htmlspecialchars($q));
    try {
        $q = $pdo_db->prepare($q);
        if (!$q) {
            $einfo = $pdo_db->errorInfo();
            throw new Exception($einfo[1] . ": " . $einfo[2]);
        }
        $time = microtime(true)-$time_start;
        $total_time += $time;
        return $q;
    }
    catch (PDOException $e) {
        if ($debug)
            print "<table class='debug_error' width='100%'><tr><td colspan='3'>".htmlspecialchars($e->getMessage())."</td></tr></table>\n";
        throw new Exception("DB Error");
    }
}
function sql_execute($res, $params) {
    global $pdo_db;
    global $total_time;
    global $total_queries;
    $debug = isset($_SESSION['debug_mode']);
    $time_start = microtime(true);

    try {
        $res->execute($params);
        $time = microtime(true)-$time_start;
        $total_time += $time;
        $total_queries++;
        if ($debug) {
            printf("<table class='debug' width='100%%'><tr><td valign='top' width='20'>%d<td>SQL: %s</td><td width='100'>%.4f сек.</td><td width='100'>%.4f сек.</td></tr></table>\n", $total_queries, "(prepared statement)", $time, $total_time);
        }
    }
    catch (PDOException $e) {
        if ($debug)
            print "<table class='debug_error' width='100%'><tr><td colspan='3'>".htmlspecialchars($e->getMessage())."</td></tr></table>\n";
        throw new Exception("DB Error");
    }
}
function sql_pe($query, $params) {
    // prepares and executes query, closes cursor
    // returns all the rows
    $res = sql_prepare($query);
    sql_execute($res, $params);
    try {
        return sql_fetchall($res);
    }
    catch (PDOException $e) {
        return array();
    }
}
