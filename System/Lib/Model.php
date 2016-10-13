<?php
// 模型类
//extends Controlle
namespace Lib;
define('PDO_DEBUG', false);

use Driver\DbPdo as DbPdo;

class Model extends Lib{

    protected $db;
    protected $config = [];
    protected $re;

    /**
     * 构造函数
     */
    public function __construct($name){
        $this->db = new DbPdo($name);

    }


    public function query($sql, $params = array()) {

        if (empty($params)) {
            $result = $this->db->exec($sql);
            return $result;
        }
        $statement = $this->db->prepare($sql);
        if (!is_object($statement)) {
            \Lib\throwexce('当前连接数据库用户没有执行该条语句的权限，请检查mysql权限配置');
            return false;
        }
        $result = $statement->execute($params);

        if (!$result) {
            return false;
        } else {
            return $statement->rowCount();
        }
    }


    public function fetchcolumn($sql, $params = array(), $column = 0) {
        $statement = $this->db->prepare($sql);
        if (!is_object($statement)) {
            \Lib\throwexce('当前连接数据库用户没有执行该条语句的权限，请检查mysql权限配置');
            return false;
        }
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
            return $statement->fetchColumn($column);
        }
    }

    public function fetch($sql, $params = array()) {
        $statement = $this->db->prepare($sql);
        if (!is_object($statement)) {
            \Lib\throwexce('当前连接数据库用户没有执行该条语句的权限，请检查mysql权限配置');
            return false;
        }
        $result = $statement->execute($params);

        if (!$result) {
            return false;
        } else {
            return $statement->fetch(\PDO::FETCH_ASSOC);
        }
    }

    public function fetchall($sql, $params = array(), $keyfield = '') {
        $statement = $this->db->prepare($sql);
        if (!is_object($statement)) {
            \Lib\throwexce('当前连接数据库用户没有执行该条语句的权限，请检查mysql权限配置');
            return false;
        }
        $result = $statement->execute($params);
        if (!$result) {
            return false;
        } else {
            if (empty($keyfield)) {
                return $statement->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $temp = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $rs = array();
                if (!empty($temp)) {
                    if(is_array($keyfield)){
                        $fieldCount = count($keyfield);
                        if($fieldCount == 1){
                            foreach ($temp as $key => $row) {
                                $rs[] = $row[$keyfield[0]];
                            }
                        } elseif ($fieldCount == 2){
                            foreach ($temp as $key => $row) {
                                $rs[$keyfield[0]] = $row[$keyfield[1]];
                            }
                        }
                    } else {
                        foreach ($temp as $key => &$row) {
                            if (isset($row[$keyfield])) {
                                $rs[$row[$keyfield]] = $row;
                            } else {
                                $rs[] = $row;
                            }
                        }
                    }
                }
                return $rs;
            }
        }
    }

    public function update($table, $data = array(), $params = array(), $glue = 'AND') {
        $fields = $this->implode($data, ',');
        $condition = $this->implode($params, $glue);
        $params = array_merge($fields['params'], $condition['params']);
        $sql = "UPDATE " . tablename($table) . " SET {$fields['fields']}";
        $sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
        return $this->query($sql, $params);
    }


    public function insert($table, $data = array(), $replace = FALSE) {
        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
        $condition = $this->implode($data, ',');
        return $this->query("$cmd " . tablename($table) . " SET {$condition['fields']}", $condition['params']);
    }

    public function insertid() {
        return $this->db->lastInsertId();
    }


    public function delete($table, $params = array(), $glue = 'AND') {
        $condition = $this->implode($params, $glue);
        $sql = "DELETE FROM " . tablename($table);
        $sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
        return $this->query($sql, $condition['params']);
    }


    public function begin() {
        $this->db->begin();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollBack();
    }



    private function implode($params, $glue = ',') {
        $result = array('fields' => ' 1 ', 'params' => array());
        $split = '';
        $suffix = '';
        if (in_array(strtolower($glue), array('and', 'or'))) {
            $suffix = '__';
        }
        if (!is_array($params)) {
            $result['fields'] = $params;
            return $result;
        }
        if (is_array($params)) {
            $result['fields'] = '';
            foreach ($params as $fields => $value) {
                $result['fields'] .= $split . "`$fields` =  :{$suffix}$fields";
                $split = ' ' . $glue . ' ';
                $result['params'][":{$suffix}$fields"] = is_null($value) ? '' : $value;
            }
        }
        return $result;
    }

    /**
     * 备份数据库
     */
    public function dbbackup($dbtable,$dbpath=DBBACKUP_PATH){
        $x=1;

        $path=$dbpath.'/'.date('Y-m-d_His'.rand(100,999));

        $filename = date("Y-m-d_H.i.s"); //存放路径，默认存放到项目最外层
        $size=2048;

        $this->query("set names 'utf8'");
        $sql = "set charset utf8;\r\n";

        foreach($dbtable as $k => $table){

            $q2 = $this->prepare("show create table `$table`")->execute()->fetch();

            $q2[0] = $q2;

            if($q2[0]['Create Table']!=""){

                $sql ='DROP TABLE IF EXISTS `'.$q2[0]['Table'].'`'.";\r\n". $q2[0]['Create Table'].";\r\n";

                foreach($this->prepare("select * from `$table`")->execute()->fetchAll() as $data){

                    $keys = array_keys($data);

                    $keys = array_map('addslashes', $keys);

                    $keys = join('`,`', $keys);

                    $keys = "`" . $keys . "`";

                    $vals = array_values($data);

                    $vals = array_map('addslashes', $vals);

                    $vals = join("','", $vals);

                    $vals = "'" . str_replace("\r\n","/r/n",$vals) . "'";

                    $sql .= "insert into `$table`($keys) values(".str_replace("''","NULL",$vals).");\r\n";


                    if(filesize($path.'/'.sprintf("%05d", $x).".sql")>($size)*1024){

                        $x++;

                    }
                    if(!fileWrite($csql.$sql,sprintf("%05d", $x).".sql",$path)){ return 0; }

                    $sql="";
                }

            }else{
                unset($k);
            }
        }
        return true;
    }

    /**
     * 恢复数据库
     */
    public function dbregain($fname,$dbpath=DBBACKUP_PATH) {

        $array=getFile($dbpath.'/'.$fname);

        foreach($array as $files){
            $sql_contents = file($dbpath.'/'.$fname.'/'.$files);
            $sql_str = '';
            foreach ($sql_contents as $k=>$line){
                $sql_str .= $line;
                if (';' == substr(rtrim($line), -1, 1)) {
                    //一条sql语句结束
                    $a =  $this->prepare(str_replace("/r/n","\r\n",trim($sql_str)))->execute();
                    if(!$a) return false;
                    unset($sql_str);
                    $sql_str = '';
                }
            }
        }
        return true;
    }
}

