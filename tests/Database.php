<?php
require_once 'PHPUnit/Framework.php';
require_once '../lib/config.php';
require_once '../lib/common.php';

class Database extends PHPUnit_Framework_TestCase {
    protected $conn;
    protected $db_schema = array();

    protected function parse_installer() {
        $arr = file('../install/install.sql');
        $cur_table = '';
        foreach ($arr as $s) {
            if (preg_match('/create table(?: if not exists) `([a-z0-9_]+)`/i', $s, $matches)) {
                $cur_table = strtolower($matches[1]);
                $this->db_schema[$cur_table] = array();
            }
            elseif (preg_match('/^\s*`([a-z0-9_]+)`/i', $s, $matches)) {
                $this->db_schema[$cur_table][] = strtolower($matches[1]);
            }
        }
    }

    protected function arrays_are_same($array1, $array2) {
        //returns false if arrays are different, true otherwise
        foreach($array1 as $k1 => $arr1) {
            if (!isset($array2[$k1])) {
                print "Error: table `$k1` doesn't exist\n";
                return false;
            }
            foreach($arr1 as $v1) {
                $i = array_search($v1, $array2[$k1]);
                if($i === false) {
                    print "Error: field `$v1` doesn't exist\n";
                    return false;
                }
                unset($array2[$k1][$i]);
            }
            if (count(array_keys($array2[$k1])) > 0) {
                print "Error: excess fields in `$k1`\n";
                return false;
            }
            unset($array2[$k1]);
        }
        if (count(array_keys($array2)) > 0) {
            return false;
            print "Error: excess tables in `$k1`\n";
        }
        return true;
    }

    public function testConnect() {
        global $config;
        $this->conn = mysql_connect($config['mysql_host'], $config['mysql_user'], $config['mysql_passwd']);
        $this->assertType('resource', $this->conn);
        $this->assertTrue(sql_query('USE '.$config['mysql_dbname']));
    }
    public function testTables() {
        $this->parse_installer();
        $this->assertTrue($this->arrays_are_same($this->db_schema, sql_get_schema()));
    }
}
?>
