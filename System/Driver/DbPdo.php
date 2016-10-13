<?php
// PDO类

namespace Driver;

class DbPdo {

	private $pdo = null;
	private $stmt;

	/**
	 * 构造方法
	 * @access	public
	 * @param	$db	type:array
	 * @return	void
	 */
	public function __construct(Array $db=[]){
		if(!is_array($db)) return false;
    	// 验证扩展是否开启
		if(!extension_loaded("Pdo_mysql")){
			throwexce(sprintf("Your PHP installation appears to be missing the %s extension which is required by Worker.", "Pdo"));
		}
		try{
			$this->pdo 	= new \PDO("mysql:host={$db['host']};dbname={$db['database']};port={$db['port']};", $db['username'], $db['password'],$db['pconnect']?array(PDO::ATTR_PERSISTENT => true):[]);
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			// $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->exec("set names utf8"); 
		}catch ( \Exception $e ){
			\Lib\throwexce($e->getMessage());
		}
		
	}

	/**
	 * 过滤
	 * @access	public
	 * @param	$sql	type:string
	 * @return	void
	 */
	public function prepare($sql){
		if($this->pdo){
			return $this->pdo->prepare($sql);
		}else{
			\Lib\throwexce(sprintf("%s statement error!","{$sql}"));
		}
	}

	/**
	 * 过滤
	 * @param	$value	type:string
	 * @access	public
	 */
	public function quote($value){
		return $this->pdo->quote($value);
	}

	/**
	 * 事务开启
	 * @access	public
	 */
	public function begin(){
		return $this->pdo->beginTransaction();
	}

	/**
	 * 事务结束
	 * @access	public
	 */
	public function commit(){
		return $this->pdo->commit();
	}

	/**
	 * 执行回滚
	 * @access	public
	 */
	public function rollBack(){
		return $this->pdo->rollBack();
	}

	/**
	 * $sql
	 * @access	public
	 * @param	$sql	type:string
	 * @return	void
	 */
	public function query($sql){
		if($this->pdo){
			return $this->pdo->query($sql);
		}else{
			\Lib\throwexce(sprintf("%s statement error!","{$sql}"));
		}
	}

	/**
	 * $sql
	 * @access	public
	 * @param	$sql	type:string
	 * @return	void
	 */
	public function exec($sql){
		if($this->pdo){
			return $this->pdo->exec($sql);
		}else{
			\Lib\throwexce(sprintf("%s statement error!","{$sql}"));
		}
	}


	public function lastInsertId(){
		if($this->pdo){
			return $this->pdo->lastInsertId();
		}
	}
	 /**
	  * 析构函数
	  */
	 public function __destruct() {
	  $this->pdo = NULL;
	 }
}