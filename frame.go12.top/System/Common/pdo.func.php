<?php
defined('TPL_INCLUDE') or die('Restricted access');
/**
 * 初始化 pdo 对象实例,主从
 * @return object->PDO
 * 主库不传参数
 */
function pdo($type='master') {
    global $_W;
    static $masterdb;
    static $slaveindex;
    static $slavedb;
    if($type == 'master'){
        if(empty($masterdb)) {
            $masterdb = new \Lib\Model($_W['config']['db']['master']);
        }
        return $masterdb;
    } else {
        if($type == 'slave'){
            if(!$slaveindex){
                $slaveindex = rand(1, 1);
                //$slaveindex = 1;
            }
            $slavekey = 'slave'.$slaveindex;
            if(empty($slavedb)){
                $slavedb = new \Lib\Model($_W['config']['db'][$slavekey]);
            }
        } else {
            //单独读服务器
            if(empty($slavedb)){
                $slavedb = new \Lib\Model($_W['config']['db'][$type]);
            }
        }
        return $slavedb;
    }
}

/**
 * 执行一条非查询语句
 *
 * @param string $sql
 * @param array or string $params
 * @return mixed
 *		  成功返回受影响的行数
 *		  失败返回FALSE
 */
function pdo_query($sql, $params = array()) {
    $sql_bak = trim($sql);
    $host = 'master';
    if(stripos($sql_bak,'select')===0){
        $host = 'slave';
    }
    return pdo($host)->query($sql, $params);
}

/**
 * 执行SQL返回第一个字段
 *
 * @param string $sql
 * @param array $params
 * @param int $column 返回查询结果的某列，默认为第一列
 * @return mixed
 */
function pdo_fetchcolumn($sql, $params = array(), $column = 0, $slavekey = '') {
    return pdo($slavekey ? $slavekey : 'slave')->fetchcolumn($sql, $params, $column);
}
/**
 * 执行SQL返回第一行
 *
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function pdo_fetch($sql, $params = array(), $slavekey = '') {
    if(checkSql($sql)){
        $slavekey = 'master';
    }
    return pdo($slavekey ? $slavekey : 'slave')->fetch($sql, $params);
}
/**
 * 执行SQL返回全部记录
 *
 * @param string $sql
 * @param array $params
 * @return mixed
 */
function pdo_fetchall($sql, $params = array(), $keyfield = '', $slavekey = '') {
    if(checkSql($sql)){
        $slavekey = 'master';
    }
    return pdo($slavekey ? $slavekey : 'slave')->fetchall($sql, $params, $keyfield);
}

/**
 * 更新记录
 *
 * @param string $table
 * @param array $data
 *		  要更新的数据数组
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param array $params
 *		  更新条件
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param string $glue
 *		  可以为AND OR
 * @return mixed
 */
function pdo_update($table, $data = array(), $params = array(), $glue = 'AND') {
//    if($table == 'fans'){
//        pdo()->update($table, $data, $params, $glue);
//        $table = $table.'_'.$weid % 10;
//    }
    return pdo()->update($table, $data, $params, $glue);
}

/**
 * 更新记录
 *
 * @param string $table
 * @param array $data
 *		  要更新的数据数组
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param boolean $replace
 *		  是否执行REPLACE INTO
 *		  默认为FALSE
 * @return mixed
 */
function pdo_insert($table, $data = array(), $replace = FALSE) {
    return pdo()->insert($table, $data, $replace);
}

/**
 * 删除记录
 *
 * @param string $table
 * @param array $params
 *		  更新条件
 *		  array(
 *			  '字段名' => '值'
 *		  )
 * @param string $glue
 *		  可以为AND OR
 * @return mixed
 */
function pdo_delete($table, $params = array(), $glue = 'AND') {
    if($table == 'fans' && $params['weid']){
        pdo()->delete($table, $params, $glue);
        $table = $table.'_'.$params['weid'] % 10;
    }
    return pdo()->delete($table, $params, $glue);
}

/**
 * 返回lastInsertId
 *
 */
function pdo_insertid() {
    return pdo()->insertid();
}

function pdo_begin() {
    pdo()->begin();
}

function pdo_commit() {
    pdo()->commit();
}

function pdo_rollback() {
    pdo()->rollBack();
}


/**
 * 返回完整数据表名(加前缀)
 * @param string $table
 * @return string
 */
function tablename($table)
{
    global $_GPC;

    if($table == 'article'){
        if(empty($_GPC['bid'])){
            $table = 'article0';
        }else{
            $table = 'article'.intval($_GPC['bid']/1000);
        }
    }

    return "`{$GLOBALS['_W']['config']['db']['tablepre']}{$table}`";
}



/**
 * 获取pdo操作错误信息列表
 * @param bool $output 是否要输出执行记录和执行错误信息
 * @param array $append 加入执行信息，如果此参数不为空则 $output 参数为 false
 * @return array
 */
function pdo_debug($output = true, $append = array()) {
    return pdo()->debug($output, $append);
}
/**
 * 执行SQL文件
 */
function pdo_run($sql) {
    return pdo()->run($sql);
}

function pdo_fieldexists($tablename, $fieldname = '') {
    return pdo()->fieldexists($tablename, $fieldname);
}

function pdo_indexexists($tablename, $indexname = '') {
    return pdo()->indexexists($tablename, $indexname);
}
/**
 * 获取所有字段,用于过滤字段
 * @param string $tablename 原始表名
 * @return array 所有表名 array('col1','col2');
 */
function pdo_fetchallfields($tablename){
    $fields = pdo_fetchall("DESCRIBE {$tablename}", array(), 'Field');
    $fields = array_keys($fields);
    return $fields;
}

function checkSql($sql){
    if(stripos($sql,'insert') !== false || stripos($sql,'update') !== false || stripos($sql,'delete') !== false){
        return true;
    }else{
        return false;
    }
}